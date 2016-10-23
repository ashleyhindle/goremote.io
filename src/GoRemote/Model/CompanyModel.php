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
		$duplicateId = $db->fetchAssoc(
			'select companyid, url, logo from companies where name=?',
			[
				(string) $this->name
			]);

		if ($duplicateId) {
            if (empty($duplicateId['logo']) && !empty($this->logo)) {
                $db->createQueryBuilder()
                    ->update('companies')
                    ->set('logo', '?')
                    ->where('companyid = ?')
                    ->setParameter(0, $this->logo)
                    ->setParameter(1, $duplicateId['companyid'])
                    ->execute();
            }
			$this->id = $duplicateId['companyid'];
			return $duplicateId['companyid'];
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