# BroRelo - Browser Auto Refresh / Reload for Firefox, Chrome & Opera

`Ubuntu 21.10 | Apache/2.4.48 | PHP 8.0.18`

Reloads windows regardless of which Browser are in them and without the need of any additional Browser-extensions for any Browser.

Currently it works with Firefox, Chrome & Opera (but it should work with all Browsers). It doesn't even needs a URL or IP to work, but a identifier in the Title `<title></title>` of the Page. And it also doesn't use any observer or services in order to work, it runs, when it's requested.

@install [xdotool - window management](https://www.semicomplete.com/projects/xdotool/)

```sh
~$ sudo apt-get install xdotool
```

---

Download `browser-reload.php` to wherever you like and make it executable

```sh
~$ chmod +x ~/bin/browser-reload/browser-reload.php
```

Set an EnvVar on your local Server and pass it to Env

```sh
~$ sudo gedit /etc/apache2/envvars

export LOCAL_MACHINE_TITLE=" local-dev-many-title"
```

```sh
~$ sudo gedit /etc/apache2/apache2.conf

PassEnv LOCAL_MACHINE_TITLE
```
---

To make a Page (window) auto reloadable, just put the EnvVar in it's Title (or the value itself). This script searches in windownames of open windows to check if they contain the specified EnvVar. If a Window matches the criteria, the key defined in `$config['trigger_key']` will get fired ("F5" | "ctrl+r").

Multiple windows with multiple Browsers at the same time are working as well - the Pages have to be active in the Windows they're in (visible, top tab), and that's it.

```php
<title>...<?= /*local*/ $_SERVER['LOCAL_MACHINE_TITLE'] ?? null ?></title>
```

---

## Visual Studio Code

To reload Browser on save, @install [vscode-run-on-save](https://github.com/pucelle/vscode-run-on-save)

`~/.config/Code/User/settings.json`

```jsonc
{
    "runOnSave.statusMessageTimeout": 1000,
    "runOnSave.commands": [
        {
            "match": ".*",

            // # Options
            // "command": "...browser-reload.php    Options=GoesHere",

            // output=false                         // [false | 'minimal'] :: default true
            // set_timeout=0.1                      // > 0                 :: default 0.1
            // trigger_key='ctrl%2Br'               // ['F5' | 'ctrl%2Br'] :: default 'ctrl+r'
            // srch_title='local-dev-many-title'    // (string)            :: default 'local-dev-many-title'

            // # set custom Browser names to search for
            // srch_browser[]=Navigator
            // srch_browser[]=Google-chrome
            // srch_browser[]=Opera

            // # the file, that has triggered the reload
            // filename=${fileBasename}
            // extension=${fileExtname}
            // file=${file}
            // dir=${fileDirname}

            "command": "~/bin/browser-reload/browser-reload.php",
            "runIn": "backend",
            "runningStatusMessage": "BrowserReload started ${fileBasename}",
            "finishStatusMessage": "BrowserReload done, ${fileBasename}"
        }
    ]
}
```
Enable / Disable via cmd `ctrl+shift+p`

```cmd
Run On Save: Enable
Run On Save: Disable
```

---

Add Aliases (optional)

```sh
~$ sudo gedit ~/.bash_aliases

alias BrowserReload='~/bin/browser-reload/browser-reload.php'
alias BrowserReloadQuiet='~/bin/browser-reload/browser-reload.php output=false'

~$ source ~/.bash_aliases
```

with Aliases in place, we can reload open windows from a terminal with

```js
~$ BrowserReload
```
