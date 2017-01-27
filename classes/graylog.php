<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Graylog/GELF log store plugin
 *
 * @package    logstore_graylog
 * @copyright  2016, Binoj David <dbinoj@gmail.com>
 * @author     Binoj David, https://www.dbinoj.com
 * @thanks     2016, Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace logstore_graylog;

defined('MOODLE_INTERNAL') || die();

/**
 * Graylog interface.
 */
class graylog
{
    private static $instance;

    private $transport;
    private $config;
    private $buffer = array();
    private $ready;
	private $data = [];

    /**
     * Constructor.
     */
    private function __construct() {
        $this->ready = false;
        try {
            if ($this->setup()) {
                $this->ready = true;
            }
        } catch (\Exception $e) { }
    }

    /**
     * Setup the connection.
     */
    private function setup() {
    	global $CFG;

        require_once(dirname(__FILE__) . '/../vendor/autoload.php');
        $this->config = get_config('logstore_graylog');

		$this->data = (object)[
			'act' => 'log',
			'host' => $CFG->wwwroot,
			'ctoken' => '[admin token]',
			'active' => '[checkbox “Dienst aktiv setzen”]',
			'title' => '[Name of Site]',
			'contact' => '[Email Kontakt]',
			'etherpadurl' => '[URL to etherpad]',
			'logo' => '[base64 encoded logo]',
			'description' => '[Beschreibung]',
			'allow_registration' => '[Checkbox “Erlaube Registrierung”]',
			'allow_course_creation' => '[Checkbox “Erlaube Management von …”]',
			'base_category' => '[“Kursbereich für eduMessenger-Gruppen”]',
			'base_course' => '[“Vorlagekurs für eduMessenger-Gruppen”]',
			'actions' => [],
		];

		// testing
		return true;

        $hostnameavailable = isset($this->config->hostname);
        $portavailable = isset($this->config->port);
        if (!$hostnameavailable or !$portavailable) {
            return false;
        }
        if ($this->config->transport == 'udp') {
            $this->transport = new \Gelf\Transport\UdpTransport(
                $this->config->hostname,
                $this->config->port,
                \Gelf\Transport\UdpTransport::CHUNK_SIZE_LAN
            );
        }
        else if ($this->config->transport == 'tcp') {
            $this->transport = new \Gelf\Transport\TcpTransport(
                $this->config->hostname,
                $this->config->port
            );
            if (isset($this->config->tcptimeout)) {
                $this->transport->setConnectTimeout($this->config->tcptimeout);
            }
        }

        return true;
    }

    /**
     * Singleton.
     */
    public static function instance() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Flush buffers.
     */
    public function dispose()
    {
        if (!empty($this->buffer)) {
            $this->flush();
        }
    }

    /**
     * Destructor.
     */
    public function __destruct() {
        $this->dispose();
    }

    /**
     * Are we ready?
     */
    public function is_ready() {
        return $this->ready;
    }

    /**
     * Is Graylog enabled?
     */
    public static function is_enabled() {
        $enabled = get_config('tool_log', 'enabled_stores');
        $enabled = array_flip(explode(',', $enabled));
        return isset($enabled['logstore_graylog']) && $enabled['logstore_graylog'];
    }

    /**
     * Log an item with Graylog.
     * @param $data JSON
     */
    public static function log($data) {
        $graylog = static::instance();
        $graylog->buffer[] = $data;
        if (count($graylog->buffer) > 100) {
            $graylog->flush();
        }
    }

    /**
     * Store a standard log item with Graylog.
     * @param $data
     */
    public static function log_standardentry($data) {
    	global $DB;

        $data = (object)$data;

		if (is_string($data->other)) {
			$tmp = unserialize($data->other);
			if ($tmp !== false) {
				$data->other = $tmp;
			}
		}

		$data->other = (object)$data->other;

		// var_dump($data);

		if (!in_array($data->eventname, [
			'\mod_forum\event\discussion_created', // forum erstellt
		])) {
			return;
		}

		$data->coursename = $DB->get_field('course', 'fullname', ['id' => $data->courseid]);

		if ($data->eventname == '\mod_forum\event\discussion_created') {
			$data->other->developer_infos = 'forumid bezieht sich auf die moodle forum aktivität und nicht auf den beitrag im forum
				discussionid = objectid';
			$data->other->forumname = $DB->get_field('forum', 'name', ['id' => $data->other->forumid]);
			$data->other->discussionid = $data->objectid;
			$data->other->discussionname = $DB->get_field('forum_discussions', 'name', ['id' => $data->objectid]);
		}

		/*
        $newrow = new \stdClass();
        foreach ($data as $k => $v) {
            if ($k == 'other') {
                $tmp = unserialize($v);
                if ($tmp !== false) {
                    $v = json_encode($tmp);
                }
            }
            if ($k == 'id') {
                $k = 'log_id';
            }
            $newrow->$k = $v;
        }
		*/
        static::log($data);
    }

    /**
     * End the buffer.
     */
    public function flush() {

        if (empty($this->buffer) || !$this->is_ready()) {
            return;
        }

		$url = 'https://www.schrenk.cc/eduMessenger/services/messenger.php';
		// $url = 'http://localhost/moodle30/admin/tool/log/store/graylog/test.server.php';

        $data = $this->data;
		$data->actions = $this->buffer;

		$ch = curl_init($url);

		try {
			# Setup request to send json via POST.
			$payload = json_encode($data);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			# Return response instead of printing.
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			# Send request.
			$result = curl_exec($ch);
			curl_close($ch);
			# Print response.

			echo "posting: ".print_r($data, true);
			echo "<pre>$result</pre>";
		} catch (\Exception $e) {
			debugging('Cannot write to Graylog: ' . $e->getMessage(), DEBUG_DEVELOPER);
		}

        $this->buffer = array();
    }
}