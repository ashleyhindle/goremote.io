<?php
namespace GoRemote\Cli;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class TweetCommand extends \Knp\Command\Command
{
	private $app;

	protected function configure()
	{
		$this
			->setName('tweet')
			->setDescription('Tweet')
			->addArgument(
				'tweet',
				InputArgument::REQUIRED,
				'What shall I tweet?'
				);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->app = $this->getSilexApplication();
		$tweetMessage = $input->getArgument('tweet');
		$output->writeln("Sending tweet: '{$tweetMessage}'");
		$this->app['twitter']->setToken($this->app['config.twitter']['token'], $this->app['config.twitter']['token_secret']);
		$tweet = [
			'status' => $tweetMessage
		];

		$reply = $this->app['twitter']->statuses_update($tweet);
		return 0; // exit code of 0 is 'successful'
	}
}
