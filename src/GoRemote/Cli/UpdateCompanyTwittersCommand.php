<?php
namespace GoRemote\Cli;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/*
Loop through a job per company, go to apply url and get the twitter username (if available)
Update companies table
*/

class UpdateCompanyTwittersCommand extends Command
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
		$jobs = $db->fetchAll('select companyid, applyurl from jobs inner join companies using(companyid) where (twitter is null or twitter="") group by companyid');

		foreach ($jobs as $job) {
			/* 
			Figure out twitter with a twitter model or something, then change the code in StackOverflow 
			to use the same code so we're not repeating ourselves

			Get the apply url, figure out the company url from that, the 
			*/
		}

		return 0; // exit code of 0 is 'successful'
	}

}