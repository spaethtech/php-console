# Terminals

## PhpStorm

This configuration will likely work on any of the IntelliJ-based IDEs, but PHPStorm makes the most sense for UCRM Plugin
development.

Navigate to the correct area of settings:

```
File | Settings | Tools | Terminal
```

_The below options append the following to the `PATH` environment variable in any newly executed Terminal in PhpStorm._
- `{PROJECT_DIR}\bin`
- `{PROJECT_DIR}\ide\bin`
- `{PROJECT_DIR}\ide\vendor\bin`
- `{PROJECT_DIR}\vendor\bin`

Choose one of the following, depending upon your preferred terminal.

> _**IMPORTANT**: Currently, PhpStorm's Terminal settings are application-wide and configuring any of the following
> will apply to ALL projects._
>
> _Due to this limitation, the commands below include a fallback in the case of a missing terminal folder or script._

## CMD
Shell Path
```
cmd /k .idea\terminals\cmd.bat || cls
```

## PowerShell 7

> _**NOTE:** Newer versions of PhpStorm occasionally issue a terminal warning if you attempt to use a PowerShell version
> prior to v3._

Available at [PowerShell 7](https://docs.microsoft.com/en-us/powershell/scripting/install/installing-powershell-on-windows?view=powershell-7.2)

Shell Path
```
pwsh -NoExit -Command ".idea\terminals\powershell.ps1 || cls"
```

## Cmder

Check out _[Cmder](https://github.com/cmderdev/cmder) for the download and instructions._

Environment Variables (if not set in PATH already)
```
CMDER_ROOT={CMDER_PATH}
```
Shell Path
```
cmd /k .idea\terminals\cmder.cmd || cls && call \"%CMDER_ROOT%\vendor\init.bat\"
```

## Git Bash

Included with the [Git for Windows](https://gitforwindows.org/) installation.

Shell Path
```
C:\Program Files\Git\bin\bash.exe --rcfile .idea/terminals/.bashrc
```
