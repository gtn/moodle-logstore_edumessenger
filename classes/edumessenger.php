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
 * eduMessenger log store plugin
 *
 * @package    logstore_edumessenger
 * @copyright  2016, Binoj David <dbinoj@gmail.com>
 * @author     Binoj David, https://www.dbinoj.com
 * @thanks     2016, Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace logstore_edumessenger;

defined('MOODLE_INTERNAL') || die();

/**
 * eduMessenger interface.
 */
class edumessenger {
	private static $instance;
	private static $debug;

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
		} catch (\Exception $e) {
		}
	}

	/**
	 * Setup the connection.
	 */
	private function setup() {
		global $CFG, $DB;

		require_once(dirname(__FILE__).'/../vendor/autoload.php');
		$this->config = get_config('logstore_edumessenger');
		$this->data = (object)[
			'actions' => [],
		];


		$dataManage = [
			'action' => 'manage',
			'host' => $CFG->wwwroot,
			'ctoken' => $this->config->admintoken,
			'active' => '1',
			'title' => $DB->get_record('course', ['id' => 1])->fullname,
			'contact' => $CFG->supportemail,
			'etherpadurl' => $this->config->etherpadurl,
			'logo' => $this->config->logofile,
			'description' => '[Beschreibung]',
			'allow_registration' => $this->config->allow_registration,
			'allow_course_creation' => $this->config->allow_course_creation,
			'base_category' => $this->config->base_category,
			'base_course' => $this->config->base_course,
		];

		$md5 = md5(json_encode([
			$this->config->serverurl,
			$dataManage
		]));
		if (true || $md5 !== @$this->config->last_manage_call_md5) {
			$this->addAction($dataManage);
			set_config('last_manage_call_md5', $md5, 'logstore_edumessenger');
		}

		// testing
		return true;

		/*
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
		} elseif ($this->config->transport == 'tcp') {
			$this->transport = new \Gelf\Transport\TcpTransport(
				$this->config->hostname,
				$this->config->port
			);
			if (isset($this->config->tcptimeout)) {
				$this->transport->setConnectTimeout($this->config->tcptimeout);
			}
		}

		return true;
		*/
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

	public static function set_debug($debug) {
		static::$debug = $debug;
	}

	/**
	 * Flush buffers.
	 */
	public function dispose() {
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
	 * Is eduMessenger enabled?
	 */
	public static function is_enabled() {
		$enabled = get_config('tool_log', 'enabled_stores');
		$enabled = array_flip(explode(',', $enabled));

		return isset($enabled['logstore_edumessenger']) && $enabled['logstore_edumessenger'];
	}

	/**
	 * Log an item with eduMessenger.
	 * @param $data JSON
	 */
	public static function log($data) {
		static::instance()->addAction([
			'action' => 'event',
			'event' => $data
		]);
	}

	public function addAction($data) {
		$this->buffer[] = $data;
		if (count($this->buffer) > 100) {
			$this->flush();
		}
	}

	/**
	 * Store a standard log item with eduMessenger.
	 * @param $data
	 */
	public static function log_standardentry($data) {
		global $CFG, $DB;

		$data = (object)$data;

		if (is_string($data->other)) {
			$tmp = unserialize($data->other);
			if ($tmp !== false) {
				$data->other = $tmp;
			}
		}

		$data->other = (object)$data->other;

		// var_dump($data);

		$data->coursename = $DB->get_field('course', 'fullname', ['id' => $data->courseid]);

		/*
		if ($data->eventname == '\mod_forum\event\discussion_created') {
			$data->other->developer_infos = 'forumid bezieht sich auf die moodle forum aktivität und nicht auf den beitrag im forum
				discussionid = objectid';
			$data->other->forumname = $DB->get_field('forum', 'name', ['id' => $data->other->forumid]);
			$data->other->discussionid = $data->objectid;
			$data->other->discussionname = $DB->get_field('forum_discussions', 'name', ['id' => $data->objectid]);
		} else
		*/
		if ($data->eventname == '\mod_forum\event\post_created') {
			require_once $CFG->dirroot.'/mod/forum/externallib.php';

			try {
				$posts = \mod_forum_external::get_forum_discussion_posts($data->objectid);
				foreach ($posts['posts'] as $post) {
					if ($post->id == $data->objectid) {
						$data->other = (object)((array)$data->other + (array)$post);
						break;
					}
				}
			} catch (\Exception $e) {
				return;
			}

			$data->other->forumname = $DB->get_field('forum', 'name', ['id' => $data->other->forumid]);
			$data->other->discussionname = $DB->get_field('forum_discussions', 'name', ['id' => $data->other->discussionid]);
		} else {
			// echo $data->eventname;
			return;
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

		$data = $this->data;
		$data->actions = $this->buffer;

		$ch = curl_init($this->config->serverurl);

		if (static::$debug) {
			echo "\n\nsending data: ";
			echo json_encode($data, JSON_PRETTY_PRINT);
		}

		try {
			# Setup request to send json via POST.
			$payload = json_encode($data);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			# Return response instead of printing.
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			if (!empty($this->config->tcptimeout)) {
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
				curl_setopt($ch, CURLOPT_TIMEOUT, 400);
			}

			# Send request.
			$result = curl_exec($ch);
			curl_close($ch);
			# Print response.

			if (static::$debug) {
				echo "\n\nresult: ".$result;
			} elseif ($result != 'ok') {
				throw new \Exception('wrong result when posting '.substr($result, 0, 40));
			}
			echo "<pre>$result</pre>";
		} catch (\Exception $e) {
			debugging('Cannot write to eduMessenger: '.$e->getMessage(), DEBUG_DEVELOPER);
		}

		$this->buffer = array();
	}
}