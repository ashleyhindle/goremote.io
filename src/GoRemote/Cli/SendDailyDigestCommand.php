<?php
namespace GoRemote\Cli;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class SendDailyDigestCommand extends \Knp\Command\Command
{
	private $app;

	protected function configure()
	{
		$this
			->setName('send-daily-digest')
			->setDescription('Send Daily Digest')
			->addArgument(
				'apikey',
				InputArgument::REQUIRED,
				'Mailchimp API Key?'
				)
			->addArgument(
				'dryrun',
				InputArgument::OPTIONAL,
				'Should we pretend to send it?'
				);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->app = $this->getSilexApplication();
		$isDryRun = (!empty($input->getArgument('dryrun'))) ? true : false;
		$apiKey = $input->getArgument('apikey');

		if (empty($apiKey)) {
			$output->writeln('Invalid API Key');
			exit(1);
		}
		
		$latestJobs = (new \GoRemote\Model\JobModel())->getLatestJobs($this->app, 86400);
		$latestJobCount = count($latestJobs);
		// Send 4pm GMT? Before work in SF, close to finishing work in the UK
		$output->writeln("Found: {$latestJobCount} latest jobs");
		
		return 0; // exit code of 0 is 'successful'
	}
}