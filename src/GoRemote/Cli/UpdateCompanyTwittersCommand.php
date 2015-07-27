<?php
namespace GoRemote\Cli;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/*
Loop through a job per company, go to apply url and get the twitter username (if available)
Update companies table
*/

class UpdateCompanyTwittersCommand extends \Knp\Command\Command
{
	protected function configure()
	{
		$this
			->setName('update-company-twitters')
			->setDescription('Update company twitters');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$db = $this->getSilexApplication()['db'];
		$jobs = $db->fetchAll('select companyid, applyurl from jobs group by companyid limit 1');
		print_r($jobs);

		return 0; // exit code of 0 is 'successful'
	}

}