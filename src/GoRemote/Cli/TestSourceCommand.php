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
				'Which source to test? - first part of the model'
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

		foreach($source->getJobs() as $job) {
			if ($jobDuplicateCount > 4) {
				$output->writeln("Too many duplicate jobs, must be up to date so stopping");
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

			$this->app['db']->insert('jobs', [
				'applyurl' => $job->applyurl,
				'position' => $job->position,
				'dateadded' => $job->dateadded,
				'description' => $job->description,
				'sourceid' => 99,
				'companyid' => 99,
				]);
		}

		return 0; // exit code of 0 is 'successful'
	}
}