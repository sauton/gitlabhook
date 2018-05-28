<?php
/**
 * @copyright © 2017 by Solazu Co.,LTD
 * @project GitLabSync
 *
 * @since 1.0
 *
 */


class WebHook
{
    const GITLAB_BASE_DIR = '/localsrc/gitsrc';
    const GITLAB_BASE_HTML = '/home/thang/xampp';


    /**
     * List of allowed action to do
     * @var array
     */
    private $actions = ['pull', 'command'];

    /**
     * List of allowed event to trigger
     * @var array
     */
    private $events = ['push', 'merge_request'];

    /**
     * List of valid request token
     * @var array
     */
    private $tokens = ['lRDipcTNFQFcJXxHFAQvWsIDbXP6qul8'];

    /**
     * @param $str
     * @param string $unique
     */
    private function log($str, $unique = '')
    {
        $logdir = __DIR__ . '/logs2';
        if (!is_dir($logdir)) {
            mkdir($logdir);
        }
        $file = $logdir . '/' . date('Ymd') . '.log';
        if (is_array($str)) {
            $str = implode(PHP_EOL, $str);
        }
        $f = fopen($file, 'a+');
        if ($f) {
            fputs($f, $str . PHP_EOL);
            fclose($f);
        }
    }

    /**
     * Start process gitlab hook request
     */
    public function start()
    {
        $output = '';
        $result = false;
        $headers = getallheaders();

        if (!empty($headers) && isset($headers['X-Gitlab-Token'])) {
            $token = $headers['X-Gitlab-Token'];
            if (in_array($token, $this->tokens)) {
                if (isset($_GET['repo']) && isset($_GET['action'])) {
                    $repo = $_GET['repo'];
                    $action = $_GET['action'];
                    if (in_array($action, $this->actions)) {
                        //Process request
                        $branch = isset($_GET['branch']) ? $_GET['branch'] : 'develop';
                        $gitpath = self::GITLAB_BASE_DIR . '/' . $repo;

                        $body = file_get_contents('php://input');
                        if (!empty($body)) {
                            $body = json_decode($body);
                            $event = $body->object_kind;
                            $refbranch = "refs/heads/{$branch}";
                            if (in_array($event, $this->events)) {
                                if (is_dir($gitpath)) {
                                    if (($body->object_kind == 'push' && $refbranch == $body->ref) ||
                                        (
                                            $body->object_kind == 'merge_request' &&
                                            $body->object_attributes->state == 'merged' &&
                                            $body->object_attributes->target_branch == $branch
                                        )
                                    ) {
                                        chdir($gitpath);
                                        $output = '';
                                        $return = 1;
//                                        $_str = exec("git checkout {$branch} && git pull", $output, $return);
                                        $_str = exec("", $output, $return_cp);
                                        if (!$return) {
                                            $result = true;
                                            if (is_dir(GITLAB_BASE_HTML . '/' . $repo)) {
                                                if (is_dir(GITLAB_BASE_DIR . '/' . $repo . '/code/theme')) {
                                                    $theme_srcgit = GITLAB_BASE_DIR . '/' . $repo . '/code/theme';
                                                    $theme_srcwww = GITLAB_BASE_HTML . '/' . $repo . '/wp-content/themes/' . $repo;
                                                    $exec = 'yes | cp -rf ' . $theme_srcgit . ' ' . $theme_srcwww;
                                                    exec($exec, $out, $ref);
                                                    $chmod = 'chmod -R 755 ' . $theme_srcwww;
                                                    exec($chmod);
                                                    if (!$ref) {
                                                        $output .= $ref . ': Coppy override ' . $theme_srcwww . ' OK <br>';
                                                    } else {
                                                        $output .= 'Cant coppy ' . $theme_srcwww . '<br>';
                                                    }
                                                } else {
                                                    $output .= GITLAB_BASE_DIR . '/' . $repo . '/code/theme' . " is not folder <br>";
                                                }
                                                if (is_dir(GITLAB_BASE_DIR . '/' . $repo . '/code/plugin')) {
                                                    $plugin_srcgit = GITLAB_BASE_DIR . '/' . $repo . '/code/plugin/*';
                                                    $plugin_srcwww = GITLAB_BASE_HTML . '/' . $repo . '/wp-content/plugins/';
                                                    $exec = 'yes | cp -rf ' . $plugin_srcgit . ' ' . $plugin_srcwww;
                                                    exec($exec, $out, $ref);
                                                    $chmod = 'chmod -R 755 ' . $plugin_srcwww;
                                                    exec($chmod);
                                                    if (!$ref) {
                                                        $output .= $ref . ': Coppy all plugin to ' . $plugin_srcwww . ' OK <br>';
                                                    } else {
                                                        $output .= 'Cant coppy ' . $plugin_srcwww . '<br>';
                                                    }

                                                } else {
                                                    $output .= GITLAB_BASE_DIR . '/' . $repo . '/plugin' . " is not folder <br>";
                                                }
                                            } else {
                                                $output .= GITLAB_BASE_HTML . '/' . $repo . ' is not folder <br>';
                                            }
                                            if (empty($output)) {
                                                $output = "Success update {$branch} of {$repo}" . $_str;
                                            } else {
                                                if (is_array($output)) {
                                                    $output = implode(PHP_EOL, $output);
                                                }
                                                $output .= $_str;
                                            }
                                        } else {
                                            $output = "Fail to update {$branch} of {$repo}";
                                        }
                                    } else {
                                        $output = ("Not expected branch : {$body->ref}");
                                    }

                                } else {
                                    $output = ("Repository {$repo} not found");
                                }

                            } else {
                                $output = ('Event did not allowed');
                            }
                        } else {
                            $output = "Empty body";
                        }
                    } else {
                        $output = ("Action did not allowed : {$action}");
                    }
                } else {
                    $output = "Not repo config";
                }
            } else {
                $output = 'Invalid token';
            }

        } else {
            $output = "Notthing";
        }
        http_response_code($result ? 200 : 400);
        $this->log($output);
        die($output);
    }
}


$hook = new WebHook();
try {
    $hook->start();
} catch (\Exception $e) {
    http_response_code(400);
}


