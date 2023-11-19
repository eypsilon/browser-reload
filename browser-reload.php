#!/usr/bin/php
<?php declare(strict_types=1); \error_reporting(E_ALL);

/**
 * @var mixed parsed arguments */
\parse_str(\implode('&', $argv), $argv);

/**
 * @var array Get usr config */
$usrConfig = \is_file($gc = __DIR__ . '/config.php') ? include_once $gc : [];

/**
 * @var array Default config, gets overwritten from "usr configs" and "args" */
$config = \array_merge([
    'output'       => true,                                    // [false | 'minimal' | true]
    'set_timeout'  => 0.1,                                     // > 0 (pause between reloads, if multiple windows)
    'trigger_key'  => 'F5',                                    // ['F5' | 'ctrl+r'] # "ctrl+r" can cause issues in VSCode (repeating "r" in editor)
    'srch_title'   => 'local-dev-many-title',                  // identifier
    'srch_browser' => ['Navigator', 'Google-chrome', 'Opera'], // Navigator = Firefox
    'match_title'  => [],                                      // see config.php.example for custom settings
    'regex_title'  => [],                                      // see config.php.example for reg expressions
], $usrConfig, $argv);


/**
 * Browser Reload / Refresh via Terminal or on filesave for Ubuntu
 *
 * @author Engin Ypsilon <engin.ypsilon@gmail.com>
 * @license http://opensource.org/licenses/mit-license.html MIT License
 */
class BrowserReload
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
     * Search classnames in windows
     *
     * @param array $a search array
     * @param array temp var
     * @return array
     */
    private function windows(array $a, array $r=[]): array
    {
        foreach($a as $b)
            $r[] = $this->xtool("search --onlyvisible --classname {$b}");
        return \explode("\n", \trim(\implode("\n", $r)));
    }

    /**
     * Regex helper
     *
     * @param string $search
     * @param array $regexe
     * @return bool true on first match
     */
    private function checkRegex(string $search, array $regexe): bool
    {
        foreach($regexe as $regex) {
            if (\preg_match($regex, $search))
                return true;
        }
        return false;
    }

    /**
     * Run
     *
     * @param array $conf
     * @param array temp var
     * @return array
     */
    function run(array $conf, array $r=[]): array
    {
        if ($d = ($conf['set_display'] ?? $this->exec('echo $DISPLAY')))
            $this->exec("export DISPLAY={$d}");
        if ($sWin = $this->windows($conf['srch_browser'])) {

            // save current active window
            $backTo = $this->xtool('getwindowfocus');
            foreach($sWin as $id) {
                $wn = $this->xtool("getwindowname $id");
                $pm = $conf['regex_title'] ? $this->checkRegex($wn, $conf['regex_title']) : false;
                if (\str_contains($wn, $conf['srch_title']) OR \in_array($wn, $conf['match_title']) OR ($pm AND $pm)) {
                    $this->xtool("windowactivate {$id}; sleep {$conf['set_timeout']}");
                    $this->xtool("key --clearmodifiers --window {$id} {$conf['trigger_key']}");
                    $r['reload'][$id] = \str_replace(" {$conf['srch_title']}", '', $wn);
                }
            }
            // reactivate where we started 10 lines ago
            $this->xtool("windowactivate {$backTo}");

            $r['count'] = [
                'windows' => \count($sWin),
                'reload'  => \count($r['reload'] ?? [])
            ];
        }
        return $r;
    }

}


/**
 * @var array run */
$run = (new BrowserReload)->run($config);

if ('false' === $config['output'])
    exit;

exit(\json_encode('minimal' === $config['output']
    ? ($run['reload'] ?? $run)
    : [
        'argv'   => $argv,
        'config' => $config,
        'window' => $run,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);
