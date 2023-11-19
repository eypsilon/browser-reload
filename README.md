# BroRelo - Browser Auto Refresh / Reload for Firefox, Chrome & Opera

`Ubuntu 21.10 | Apache/2.4.48 | PHP 8.0.18`

This package can reload windows regardless of which Browser are in them and without the need of any additional Browser-extensions for any Browser.

Currently it works with Firefox, Chrome, Brave & Opera (but it should work with all Browsers). It doesn't even needs a URL or IP to work, but a identifier in the Title `<title></title>` of the Page. And it also doesn't use an observer or services in order to work, it runs, when it's requested.

@install [xdotool - window management](https://www.semicomplete.com/projects/xdotool/)

```sh
sudo apt-get install xdotool
```
---

@install `browser-reload`

```sh
# create directory, if not exists
mkdir -p ~/bin/browser-reload

# enter directory
cd ~/bin/browser-reload

# get browser-reload
git clone https://github.com/eypsilon/browser-reload.git

# make it executable
chmod +x ~/bin/browser-reload/browser-reload.php
```

To restrict the script to your local environment, set an EnvVar on your local Server and pass it to Env. For Apache2:

```sh
sudo gedit /etc/apache2/envvars
# append
export LOCAL_MACHINE_TITLE=" local-dev-many-title"
```

```sh
sudo gedit /etc/apache2/apache2.conf
# put
PassEnv LOCAL_MACHINE_TITLE
```

See `config.php.example` to set custom default configs and handle error occurrences.

---

To now make a Page (window) auto reloadable, just put the EnvVar in it's Title (or the value itself). This script searches in windownames of open windows to check if they contain the specified EnvVar. If a Window matches the criteria, the key defined in `$config['trigger_key']` will get fired ("F5" | "ctrl+r").

Multiple windows with multiple Browsers at the same time are working as well - the Pages have to be active in the windows they're in (visible, top tab), and that's it.

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

            // output=true                          // [false | 'minimal'] :: default true
            // set_timeout=0.1                      // > 0                 :: default 0.1
            // trigger_key='ctrl%2Br'               // ['F5' | 'ctrl%2Br'] :: default 'ctrl+r'
            // srch_title='local-dev-many-title'    // (string)            :: default 'local-dev-many-title'

            // # set custom Browser names to search for. To get
            // # the Name of a window, run "xprop | grep WM_CLASS"
            // # and click the window of interest. Defaults are
            // srch_browser[]=Navigator
            // srch_browser[]=Google-chrome
            // srch_browser[]=Opera

            // # Additional titles to search for, on error for example
            // # see config.php.example for more infos
            // match_title[]='Mozilla Firefox'

            // # Add regexe, see config.php.example for more infos
            // regex_title[]='~localhost.loc~'

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

Check response output: `ctrl+k + ctrl+h` > Output > "Run on Save"

---

### Custom Config

Use custom configs to overwrite default configs. Set a list of strings to handle errors on your Page.

---

#### Regular Expressions

Set regular expressions to handle error pages. See 'config.php.example' for more infos.

---

##### Add Aliases (optional)

```sh
~$ sudo gedit ~/.bash_aliases

alias BrowserReload='~/bin/browser-reload/browser-reload.php'
alias BrowserReloadQuiet='~/bin/browser-reload/browser-reload.php output=false'

~$ source ~/.bash_aliases
```

with Aliases in place, we can reload open windows from the terminal with

```js
BrowserReload
```
