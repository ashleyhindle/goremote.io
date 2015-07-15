<?php
namespace GoRemote\Cli;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class TestSourceCommand extends \Knp\Command\Command
{
	private $app;

	protected function configure()
	{
		$this
			->setName('test-source')
			->setDescription('Test a source')
			->addArgument(
				'source',
				InputArgument::REQUIRED,
				'Which source to test? - WeWorkRemotely, Wfh'
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
			if ($jobDuplicateCount > 4) {
				$output->writeln("Too many duplicate jobs, must be up to date (unless they're not in order) so stopping");
				break;
			}

			$jobDuplicate = $this->app['db']->fetchColumn(
				'select jobid from jobs where dateadded=? and sourceid=? and applyurl=?',
				[
					(string) $job->dateadded,
					(int) $job->sourceid,
					(string) $job->applyurl
				]);

			if ($jobDuplicate) {
				$jobDuplicateCount++;
				continue;
			}

			$job->companyid = (array_key_exists($job->companyname, $companies)) 
				? $companies[$job->companyname]['companyid'] : false;

			if (empty($job->companyid)) {
				$this->app['db']->insert('companies', [
					'name' => $job->companyname,
					'dateadded' => $job->dateadded,
					'logo' => $job->companylogo
				]);

				$job->companyid = $this->app['db']->lastInsertId();				
			}

			$this->app['db']->insert('jobs', [
				'applyurl' => $job->applyurl,
				'position' => $job->position,
				'dateadded' => $job->dateadded,
				'description' => html_entity_decode($job->description),
				'sourceid' => $job->sourceid,
				'companyid' => $job->companyid,
				]);

			$sourceName = $className::SOURCE_NAME;
			$output->writeln("Inserted job for {$job->position} from {$job->companyname} from {$sourceName}");
		}

		return 0; // exit code of 0 is 'successful'
	}
}