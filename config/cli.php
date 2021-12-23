<?php

use App\Console\Command;

return function (App\Event\BuildConsoleCommands $event) {
    $event->addAliases([
        'azuracast:backup'                    => Command\Backup\BackupCommand::class,
        'azuracast:restore'                   => Command\Backup\RestoreCommand::class,
        'azuracast:debug:optimize-tables'     => Command\Debug\OptimizeTablesCommand::class,
        'azuracast:internal:auth'             => Command\Internal\DjAuthCommand::class,
        'azuracast:internal:djoff'            => Command\Internal\DjOffCommand::class,
        'azuracast:internal:djon'             => Command\Internal\DjOnCommand::class,
        'azuracast:internal:feedback'         => Command\Internal\FeedbackCommand::class,
        'azuracast:internal:sftp-event'       => Command\Internal\SftpEventCommand::class,
        'azuracast:internal:sftp-auth'        => Command\Internal\SftpAuthCommand::class,
        'azuracast:internal:nextsong'         => Command\Internal\NextSongCommand::class,
        'azuracast:internal:ip'               => Command\Internal\GetIpCommand::class,
        'azuracast:locale:generate'           => Command\Locale\GenerateCommand::class,
        'azuracast:locale:import'             => Command\Locale\ImportCommand::class,
        'azuracast:queue:process'             => Command\MessageQueue\ProcessCommand::class,
        'azuracast:queue:clear'               => Command\MessageQueue\ClearCommand::class,
        'azuracast:settings:list'             => Command\Settings\ListCommand::class,
        'azuracast:settings:set'              => Command\Settings\SetCommand::class,
        'azuracast:account:list'              => Command\Users\ListCommand::class,
        'azuracast:account:login-token'       => Command\Users\LoginTokenCommand::class,
        'azuracast:account:reset-password'    => Command\Users\ResetPasswordCommand::class,
        'azuracast:account:set-administrator' => Command\Users\SetAdministratorCommand::class,
        'azuracast:cache:clear'               => Command\ClearCacheCommand::class,
        'azuracast:setup:initialize'          => Command\InitializeCommand::class,
        'azuracast:config:migrate'            => Command\MigrateConfigCommand::class,
        'azuracast:setup:fixtures'            => Command\SetupFixturesCommand::class,
        'azuracast:setup'                     => Command\SetupCommand::class,
        'azuracast:radio:restart'             => Command\RestartRadioCommand::class,
        'azuracast:sync:run'                  => Command\SyncCommand::class,
        'azuracast:media:reprocess'           => Command\ReprocessMediaCommand::class,
        'azuracast:api:docs'                  => Command\GenerateApiDocsCommand::class,
        'locale:generate'                     => Command\Locale\GenerateCommand::class,
        'locale:import'                       => Command\Locale\ImportCommand::class,
        'queue:process'                       => Command\MessageQueue\ProcessCommand::class,
        'queue:clear'                         => Command\MessageQueue\ClearCommand::class,
        'cache:clear'                         => Command\ClearCacheCommand::class,
        'sync:run'                            => Command\SyncCommand::class,
    ]);
};
