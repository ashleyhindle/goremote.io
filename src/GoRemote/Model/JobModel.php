<?php
namespace GoRemote\Model;

class JobModel
{
	public $jobid = 0;
	public $applyurl;
	public $position;
	public $dateadded;
	public $description;
	public $sourceid;
	//TODO: Make ->company a class/model of Company instead of these silly variables
	public $companyid;
	public $companyname;
	public $companylogo;

	public function insert(\Doctrine\DBAL\Connection $db)
	{

		$jobDuplicate = $db->fetchColumn(
			'select jobid from jobs where (dateadded=? and sourceid=? and applyurl=?) or (position=? and companyid=?)',
			[
				(string) $this->dateadded,
				(int) $this->sourceid,
				(string) $this->applyurl,
				(string) $this->position,
				(int) $this->companyid
			]);

		if ($jobDuplicate) {
			return false;
		}

		$this->description = $string = preg_replace('/(<br\/>){2,}/','<br/>', html_entity_decode(trim(strip_tags(str_replace(
			['<div>', '</div>', '<br />', "\n\n"],
			['', "<br/>", "<br/>", "<br/>"], $this->description), '<b><strong><ul><li><br><br/><br />'))));

		$db->insert('jobs', [
			'applyurl' => $this->applyurl,
			'position' => $this->position,
			'dateadded' => $this->dateadded,
			'description' => $this->description,
			'sourceid' => $this->sourceid,
			'companyid' => $this->companyid,
			]);

		return $db->lastInsertId();
	}
}