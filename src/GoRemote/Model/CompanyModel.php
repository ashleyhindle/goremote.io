<?php
namespace GoRemote\Model;

class CompanyModel
{
	public $id = 0;
	public $name;
	public $url = '';
	public $twitter;
	public $logo;

	public function insert(\Doctrine\DBAL\Connection $db)
	{
		$duplicateId = $db->fetchColumn(
			'select companyid from companies where name=?',
			[
				(string) $this->name
			]);

		if ($duplicateId) {
			$this->id = $duplicateId;
			return $duplicateId;
		}

		$company = [
			'name' => $this->name,
			'dateadded' => date('Y-m-d H:i:s'),
			'logo' => $this->logo,
			'twitter' => $this->twitter,
			'url' => $this->url
		];

		$db->insert('companies', $company);
		$this->id = $db->lastInsertId();
		return $this->id;
	}
}