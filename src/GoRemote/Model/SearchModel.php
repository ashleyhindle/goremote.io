<?php
namespace GoRemote\Model;

class SearchModel
{
	public function search(\GoRemote\Application $app, $query)
	{
		$searchQuery = '%' . addcslashes($query, "%_") . '%';

        //select * from jobs inner join companies using(companyid) inner join sources using(sourceid) where match(companies.name, sources.name, jobs.position, jobs.description) against ('php' IN NATURAL LANGUAGE MODE);
        $jobs = $app['db']->fetchAll(
        "select jobs.*, unix_timestamp(jobs.dateadded) as dateadded_unixtime, companies.name as companyname, companies.url as companyurl, sources.name as sourcename, sources.url as sourceurl  from jobs 
        inner join companies using(companyid) 
        inner join sources using(sourceid) 
        where jobs.dateadded > UTC_TIMESTAMP() - INTERVAL 2 MONTH
        and jobs.datedeleted=0 
	and jobs.position <> '' 
        and (
            companies.name like ? or 
            jobs.position like ? or 
            jobs.description like ?
            )
        order by jobs.dateadded desc limit 80",
        [
            $searchQuery,
            $searchQuery,
            $searchQuery
        ]
        );

        return $jobs;
	}
}
