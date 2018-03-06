<?php
namespace GoRemote\Model;

use Doctrine\DBAL\Connection;

class HackerNewsModel extends JobModel implements SourceInterface
{
	const SOURCE_URL = 'https://hacker-news.firebaseio.com/v0/item/{ITEM}.json?print=pretty';
	const SOURCE_NAME = 'hackernews';
	const SOURCE_ID = 10;

	private $processedItems = [];
	private $db;

    /**
     * HackerNewsModel constructor.
     * @param Connection $db
     */
	public function __construct(Connection $db)
	{
	    parent::__construct();
		$this->db = $db;
		$this->processedItems = array_column($this->db->fetchAll('select itemid from hackernews'), 'itemid');
	}

    /**
     * @param $item
     * @return string
     */
	private function getFirebaseItem($item)
	{
		return json_decode(
			file_get_contents(
				str_replace(
					'{ITEM}', $item, static::SOURCE_URL
				)
			),
			true
		);
	}

    /**
     * @param array $chars
     * @param array $separators
     * @return bool|string
     */
	private function getValidSeparators(array $chars, array $separators)
	{
		$valid = false;
		foreach($separators as $sep) {
			if(array_key_exists(ord($sep), $chars) && $chars[ord($sep)] >= 2) {
				$valid = $sep;
				break;
			}
		}

		return $valid;
	}

    /**
     * @return array
     */
	public function getJobs()
	{
		$jobs = [];
		$json = $this->getJobsJson();
		$tz = new \DateTimeZone('Europe/London');
		$kids = $json['kids'];
		$notRemoteRegex = "/(?<!no)(?<!not) ?remote/i";

		foreach($kids as $item) {
		    $alreadyProcessed = in_array($item, $this->processedItems);

			if ($alreadyProcessed) {
				echo '*';
				continue;
			}

			$kid = $this->getFirebaseItem($item);
            $missingData = (array_key_exists('text', $kid) === false);

			if ($missingData) {
				$this->markProcessed($item);
				continue;
			}

            $job = new JobModel();

            $kid['text'] = $this->standardiseText($kid['text']);
			$kid['firstline'] = implode("\n", array_slice(explode("\n", $kid['text']), 0, 1));
			$chars = count_chars($kid['firstline'], 1);

			$separator = $this->getValidSeparators($chars, ['|', '-', '•']);

			$notRemote = preg_match($notRemoteRegex, $kid['firstline']) === 0;

			if($notRemote) {
                echo '.';
                $this->markProcessed($item);
                continue;
            }

            // No separator to separate company name and position (Google | Senior Engineer | REMOTE or Ireland)
            if(!$separator) {
				echo '-';
				$this->markProcessed($item);
				continue;
			}

			echo '#_#';

			$jobExtractionRegex = "/\s*(?P<company>[^|]+?)\s*\|\s*(?P<title>[^|]+?)\s*\|\s*(?P<locations>[^|]+?)\s*(?:\|\s*(?P<attrs>.+))?$/";
			$jobExtractionResult = preg_match($jobExtractionRegex, $kid['firstline'], $extractionMatch);

			if (!$jobExtractionResult) {
			    echo '&';
                $this->markProcessed($item);
                continue;
            }

			$job->position = $extractionMatch['title'];

			$job->applyurl = 'https://news.ycombinator.com/item?id=' . $item;
			$job->dateadded = (string) (new \DateTime())->setTimestamp($kid['time'])->setTimezone($tz)->format('Y-m-d H:i:s');
			$job->description = $kid['text'];
			$job->sourceid = self::SOURCE_ID;

			$job->company->name = $extractionMatch['company'];

			$jobs[] = $job;

			$this->markProcessed($item);

			if (count($jobs) > 2) {
				break;
			}
		}

		return $jobs;
	}

	private function markProcessed($itemid)
	{
		return $this->db->insert('hackernews',
				[
					'itemid' => $itemid
				]
			);
	}

	protected function getJobsJson()
	{
		return $this->getFirebaseItem(16492994); // 16492994 is March 2018
	}
}
