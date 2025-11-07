<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Profile\Console\Command;

use Magento\Framework\Console\Cli;
use SoftCommerce\Profile\Api\Service\PurgeProfileDataInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command to purge profile data from softcommerce_* tables
 */
class PurgeProfileDataCommand extends Command
{
    private const string COMMAND_NAME = 'profile:data:purge';
    private const string PROFILE_ID = 'profile-id';
    private const string FORCE = 'force';

    /**
     * @param PurgeProfileDataInterface $purgeProfileData
     * @param string|null $name
     */
    public function __construct(
        private readonly PurgeProfileDataInterface $purgeProfileData,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Purge data from softcommerce_* tables (except softcommerce_profile_entity)');
        $this->setDefinition([
            new InputOption(
                self::PROFILE_ID,
                null,
                InputOption::VALUE_OPTIONAL,
                'Profile ID to purge data for specific profile'
            ),
            new InputOption(
                self::FORCE,
                '-f',
                InputOption::VALUE_NONE,
                'Force purge without confirmation'
            )
        ]);
        $this->setHelp(
            <<<HELP
This command purges data from softcommerce_* tables while preserving profile entities.

<info>Usage examples:</info>
  <comment>Purge all profile data:</comment>
  bin/magento profile:data:purge

  <comment>Purge data for specific profile:</comment>
  bin/magento profile:data:purge --profile-id=1

  <comment>Force purge without confirmation:</comment>
  bin/magento profile:data:purge --force

<info>Tables affected:</info>
HELP
            . PHP_EOL . implode(PHP_EOL, array_map(fn($table) => "  - $table", $this->purgeProfileData->getTablesToPurge()))
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->purgeProfileData->canPurge()) {
            $output->writeln('<error>Purge operation is not allowed at this time.</error>');
            return Cli::RETURN_FAILURE;
        }

        $profileId = $input->getOption(self::PROFILE_ID) ? (int) $input->getOption(self::PROFILE_ID) : null;
        $force = $input->getOption(self::FORCE);

        if (!$force) {
            $helper = $this->getHelper('question');
            $message = $profileId
                ? "Are you sure you want to purge all data for profile ID $profileId? This action cannot be undone. [y/N] "
                : "Are you sure you want to purge ALL profile data? This action cannot be undone. [y/N] ";
            $question = new ConfirmationQuestion("<fg=magenta>$message</>", false);

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<comment>Operation cancelled.</comment>');
                return Cli::RETURN_SUCCESS;
            }
        }

        try {
            $output->writeln('<info>Starting profile data purge...</info>');

            $this->purgeProfileData->execute($profileId);

            $message = $profileId
                ? "Successfully purged data for profile ID $profileId"
                : "Successfully purged all profile data";
            $output->writeln("<info>$message</info>");

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }
    }
}
