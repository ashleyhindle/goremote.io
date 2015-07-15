drop table if exists `jobs`;
create table jobs (
	jobid bigint unsigned not null auto_increment, primary key(jobid),
	applyurl varchar(255) not null default '',
	position varchar(255) not null default '',
	dateadded datetime not null default 0, index(dateadded),
	datedeleted datetime not null default 0,
	description text not null,
	sourceid smallint unsigned not null default 0, index(sourceid),
	companyid int unsigned not null default 0
);

drop table if exists `companies`;
create table companies (
	companyid bigint unsigned not null auto_increment, primary key(companyid),
	name varchar(255) not null default '',
	url varchar(255) not null default '',
	logo varchar(255) not null default '',
	dateadded datetime not null default 0,
	datedeleted datetime not null default 0
);

drop table if exists `sources`;
create table sources (
	sourceid bigint unsigned not null auto_increment, primary key(sourceid),
	url varchar(255) not null default '',
	name varchar(255) not null default '',
	dateadded datetime not null default 0,
	datedeleted datetime not null default 0,
	enabled tinyint unsigned not null default 0
);

insert into sources (sourceid, url, name, dateadded, enabled) VALUES 
	(1, 'https://weworkremotely.com', 'WeWorkRemotely', UTC_TIMESTAMP(), 1),
	(2, 'https://wfh.io', 'WFH.io', UTC_TIMESTAMP(), 1),
	(3, 'https://jobs.github.com', 'GitHub Jobs', UTC_TIMESTAMP(), 1)
	;