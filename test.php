<?php
$repo = $_GET['repo'];
const GITLAB_BASE_DIR = '/home/thang/solazu-git';
const GITLAB_BASE_HTML = '/home/thang/xampp';
if (is_dir(GITLAB_BASE_HTML . '/' . $repo)) {
    $output='';
    if (is_dir(GITLAB_BASE_DIR . '/' . $repo . '/code/theme')) {
        $theme_srcgit = GITLAB_BASE_DIR . '/' . $repo . '/code/theme';
        $theme_srcwww = GITLAB_BASE_HTML . '/' . $repo . '/wp-content/themes/' . $repo;
        $exec = 'yes | cp -rf ' . $theme_srcgit . ' ' . $theme_srcwww;
        exec($exec, $out, $ref);
        $chmod = 'chmod -R 755 ' . $theme_srcwww;
        exec($chmod);
        if(!$ref){
            $output .= $ref . ': Coppy override '.$theme_srcwww.' OK <br>';
        }else{
            $output .= 'Cant coppy '.$theme_srcwww.'<br>';
        }
    }else{
        $output.= GITLAB_BASE_DIR . '/' . $repo . '/code/theme'." is not folder <br>";
    }
    if (is_dir(GITLAB_BASE_DIR . '/' . $repo . '/plugin')) {
        $plugin_srcgit = GITLAB_BASE_DIR . '/' . $repo . '/plugin/*';
        $plugin_srcwww = GITLAB_BASE_HTML . '/' . $repo . '/wp-content/plugins/';
        $exec='yes | cp -rf ' . $plugin_srcgit . ' ' . $plugin_srcwww;
        exec($exec, $out, $ref);
        $chmod = 'chmod -R 755 ' . $plugin_srcwww;
        exec($chmod);
        if(!$ref){
            $output .= $ref . ': Coppy all plugin to '.$plugin_srcwww.' OK <br>';
        }else{
            $output .= 'Cant coppy '.$plugin_srcwww.'<br>';
        }

    }else{
        echo GITLAB_BASE_DIR . '/' . $repo . '/plugin'." is not folder <br>";
    }
}else{
    echo GITLAB_BASE_HTML . '/' . $repo.' is not folder <br>';
}
echo $output;