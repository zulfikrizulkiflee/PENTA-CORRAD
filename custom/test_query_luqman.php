<?php

include('../system_prerequisite.php');

$mem = new Memcache();
$mem->addServer("127.0.0.1", 11211);

print_r($mem);

$pageNo = 1;
$recsPerPage = 30000;

$query = '
	select emp.* from 
	(
		select
			a.emp_no, 
			a.first_name as "First Name",
			a.last_name as "Last Name",
			b.dept_name as "Department",
			d.salary as "Salary"
		from
			employee.employees a
				left join
				employee.dept_emp c 
					left join
					employee.departments b
					on c.dept_no = b.dept_no
				on a.emp_no = c.emp_no and c.from_date = (select max(from_date) from employee.dept_emp where emp_no = a.emp_no),
			employee.salaries d
		where
			a.emp_no = d.emp_no
			and	d.from_date = (select max(from_date) 
								from employee.salaries 
								where emp_no = a.emp_no)
		limit '.$pageNo*$recsPerPage.','.$recsPerPage.' 	
	) q
JOIN  employee.employees emp on emp.emp_no = q.emp_no
order by
	emp.first_name asc, emp.last_name asc ';


$querykey = "KEY" . md5($query);
$queryRs = $mem->get($querykey);

if ($queryRs) {
    print "<p>Data was: " . $result[0] . "</p>";
    print "<p>Caching success!</p><p>Retrieved data from memcached!</p>";
} else {
	
	$queryRs = $myQuery->query($query,'SELECT','NAME');
	 $mem->set($querykey, $queryRs, 10);

   // $result = mysql_fetch_array(mysql_query($query)) or die(mysql_error());
   // $mem->set($querykey, $result, 10);
   // print "<p>Data was: " . $result[0] . "</p>";
    print "<p>Data not found in memcached.</p><p>Data retrieved from MySQL and stored in memcached for next time.</p>";
}


echo '<pre>';
echo count($queryRs).' records';
echo '<br>';
echo 'Fetched in '.(microtime(true)- $_SERVER["REQUEST_TIME_FLOAT"]).' secs';
echo '<br>';
print_r($queryRs);
echo '</pre>';
