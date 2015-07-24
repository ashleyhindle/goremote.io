<?php
namespace GoRemote\Cli;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class CheckSourceCommand extends \Knp\Command\Command
{
	private $app;

	protected function configure()
	{
		$this
			->setName('check-source')
			->setDescription('Check a source')
			->addArgument(
				'source',
				InputArgument::REQUIRED,
				'Which source to check? - WeWorkRemotely, Wfh, GitHub, StackOverflow, AuthenticJobs, Jobspresso'
				);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->app = $this->getSilexApplication();

		$source = $input->getArgument('source');
		$output->writeln("Testing Source: {$source}");
		$modelFile = $this->getProjectDirectory() . '/Model/' . $source . 'Model.php';
		if (!file_exists($modelFile) || !is_readable($modelFile)) {
			$output->writeln("File ({$modelFile}) doesn't exist or isn't readable - did you use the right source?");
			return 1; // exit code of 1 is 'not successful'
		}

		$jobDuplicateCount = 0;
		require_once $modelFile;
		$className = "\\GoRemote\\Model\\{$source}Model";
		$source = new $className;

		$companies = [];
		$companiesFromDb = $this->app['db']->fetchAll('select * from companies');
		foreach ($companiesFromDb as $c) {
			$companies[$c['name']] = $c;
		}
		unset($companiesFromDb);
 

		foreach($source->getJobs() as $job) {
			if ($jobDuplicateCount > 1) {
				$output->writeln("Too many duplicate jobs, must be up to date (unless they're not in order) so stopping");
				break;
			}

			$job->companyid = (array_key_exists($job->companyname, $companies)) 
				? $companies[$job->companyname]['companyid'] : false;

			if (empty($job->companyid)) {
				$company = [
					'name' => $job->companyname,
					'dateadded' => $job->dateadded,
					'logo' => $job->companylogo
				];

				$this->app['db']->insert('companies', $company);
				$job->companyid = $this->app['db']->lastInsertId();

				$company['companyid'] = $job->companyid;
				$companies[$company['name']] = $company; // Add to the cach array above
			}

			$job->position = preg_replace('/looking for an?/i', '', $job->position);

			$jobid = $job->insert($this->app['db']);

			if ($jobid) {
				$sourceName = $className::SOURCE_NAME;
				$output->writeln("Inserted job ({$jobid}) for {$job->position} from {$job->companyname} from {$sourceName}");
				$job->tweet($this->app);
				exit;
			} else {
				$jobDuplicateCount++;
			}
		}

		return 0; // exit code of 0 is 'successful'
	}
}