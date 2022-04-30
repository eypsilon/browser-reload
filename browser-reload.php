#!/usr/bin/env php
<?php declare(strict_types=1); \error_reporting(E_ALL);

/**
 * @var mixed parsed argv */
\parse_str(\implode('&', $argv), $argv);

/**
 * @var array Config */
$config = \array_merge([
    'set_timeout'  => 0.1,                                     // > 0 (pause between reloads, if multiple windows)
    'trigger_key'  => 'ctrl+r',                                // F5 | ctrl+r
    'srch_title'   => 'local-dev-many-title',                  // identifier
    'srch_browser' => ['Navigator', 'Google-chrome', 'Opera'], // Navigator = Firefox
    'output'       => true,                                    // output [false | minimal | true]
], $argv);


/**
 * Browser Reload / Refresh via Terminal or on filesave for Ubuntu
 *
 * @author Engin Ypsilon <engin.ypsilon@gmail.com>
 * @license http://opensource.org/licenses/mit-license.html MIT License
 */
class XdoTool
{

    /**
     * Standard exec
     *
     * @param string $cmd
     * @return string|null
     */
    private function exec(string $cmd): ?string
    {
        return \shell_exec($cmd);
    }

    /**
     * Standard xdotool
     *
     * @param string $cmd
     * @return string
     */
    private function xtool(string $cmd): string
    {
        return \trim((string) $this->exec("xdotool {$cmd}"));
    }

    /**
     * Search windows in classnames
     *
     * @param array $arr search array
     * @param array $r temp var
     * @return array
     */
    private function windows(array $arr, $r=[]): array
    {
        foreach($arr as $b)
            $r[] = $this->xtool("search --classname {$b}");
        return \explode("\n", \trim(\implode("\n", $r)));
    }

    /**
     * Run
     *
     * @param array $conf
     * @param array $r temp var
     * @return array
     */
    function run(array $conf, array $r=[]): array
    {
        if ($d = ($conf['set_display'] ?? $this->exec('echo $DISPLAY')))
            $this->exec("export DISPLAY={$d}");
        if ($searchWindows = $this->windows($conf['srch_browser']) AND $conf['set_timeout'] > 0) {

            // current active window
            $backTo = $this->xtool('getwindowfocus');
            foreach($searchWindows as $id) {
                $wn = $this->xtool("getwindowname $id");
                if (\str_contains($wn, $conf['srch_title'])) {
                    $this->xtool("windowactivate {$id}; sleep {$conf['set_timeout']}");
                    $this->xtool("key --clearmodifiers --window {$id} {$conf['trigger_key']}");
                    $r['windows'][$id] = \str_replace(" {$conf['srch_title']}", '', $wn);
                }
            }
            // reactivate where we started 9 lines ago
            $this->xtool("windowactivate {$backTo}");

            $r['count'] = [
                'windows' => \count($searchWindows),
                'reload'  => \count($r['windows'] ?? [])
            ];
        }
        return $r;
    }

}


/**
 * @var array run */
$run = (new XdoTool)->run($config);

if ('false' === $config['output'])
    exit;

exit(\json_encode('minimal' === $config['output']
    ? ($run['windows'] ?? $run)
    : [
        'argv'   => $argv,
        'config' => $config,
        'reload' => $run,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);
