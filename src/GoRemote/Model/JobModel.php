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
	public $companyid;
}


/*create table jobs (
	jobid bigint unsigned not null auto_increment, primary key(jobid),
	applyurl varchar(255) not null default '',
	position varchar(255) not null default '',
	dateadded datetime not null default 0, index(dateadded)
	datedeleted datetime not null default 0,
	description text not null default '',
	sourceid smallint unsigned not null default 0, index(sourceid),
	companyid int unsigned not null default 0
);*/