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
				'Mailchimp API Key'
				)
			->addArgument(
				'listid',
				InputArgument::REQUIRED,
				'Mailchimp list_id'
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
		$listId = $input->getArgument('listid');

		if (empty($apiKey)) {
			$output->writeln('Invalid API Key');
			return 0;
		}

		if (empty($listId)) {
			$output->writeln('Invalid List Id'); // These should be checked/asserted in the 'addArgument' above
			return 0;
		}

		if ($isDryRun) {
			$output->writeln('This is a dry run..');
		}

		$date = new \DateTime('today');
		$date->setTimezone(new \DateTimeZone('UTC'));

		$latestJobs = (new \GoRemote\Model\JobModel())->getLatestJobs($this->app, 86400);
		$latestJobCount = count($latestJobs);
		// Send 4pm GMT? Before work in SF, close to finishing work in the UK
		$output->writeln("Found: {$latestJobCount} latest jobs");

		if (empty($latestJobCount)) {
			$output->writeln("Not enough jobs to send an email campaign, quitting..");
			return 0;
		}

		$MailChimp = new \Drewm\MailChimp($apiKey);

		//print_r($MailChimp->call('/lists/list'));
		//exit(1);

		$createResponse = $MailChimp->call('/campaigns/create',
			[
				'type' => 'regular',
				'options' => [
					'list_id' => $listId,
					'title' => 'GoRemote.io Daily Digest ' . $date->format('Y-m-d'),
					'subject' => 'GoRemote.io Daily Digest ' . $date->format('jS \of M'),
					'from_email' => 'hey@goremote.io',
					'from_name' => 'GoRemote.io',
					'to_name' => '*|EMAIL|*',
					'template_id' => 272225,
					'authenticate' => true
				],
				'content' => [
					'sections' => [
						'std_content00' => $this->app['twig']->render(
								'daily-digest-email.html.twig',
								[
									'latestJobs' => $latestJobs
								]
							)
					]
				]
			]);

		print_r($createResponse);

		if (!empty($createResponse['id'])) {
			$campaignId = $createResponse['id'];

			if ($isDryRun) {
				$output->writeln("Dry run: Created campaign, but not sending");
			} else {
				$sendResponse = $MailChimp->call('/campaigns/send',
					[
						'cid' => $campaignId
					]);
				print_r($sendResponse);
			}
		} else {
			print_r($createResponse);
			$output->writeln('Something went wrong, we couldn\'t create the campaign so we haven\'t attempted to send it');
			return 1;
		}
		
		return 0; // exit code of 0 is 'successful'
	}
}