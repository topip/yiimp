<?php

$user = getuserparam(getparam('address'));
if(!$user) return;

echo "<div class='main-left-box'>";
echo "<div class='main-left-title'>Miners: $user->username</div>";
echo "<div class='main-left-inner'>";

echo "<table  class='dataGrid2'>";
echo "<thead>";
echo "<tr>";
echo "<th align=left>Summary</th>";
echo "<th align=right>Miners</th>";
echo "<th align=right>Shares</th>";
echo "<th align=right width=80>Hashrate*</th>";
echo "<th align=right width=60>Reject*</th>";
echo "</tr>";
echo "</thead>";

foreach(yaamp_get_algos() as $algo)
{
//	debuglog($algo);
	$user_rate1 = yaamp_user_rate($user->id, $algo);
	$user_rate1_bad = yaamp_user_rate_bad($user->id, $algo);

	$percent_bad = ($user_rate1 + $user_rate1_bad)? $user_rate1_bad * 100 / ($user_rate1 + $user_rate1_bad): 0;
	$percent_bad = $percent_bad? round($percent_bad, 1).'%': '';

	$user_rate1 = $user_rate1? Itoa2($user_rate1).'h/s': '-';
	$minercount = getdbocount('db_workers', "userid=$user->id and algo=:algo", array(':algo'=>$algo));

	$user_shares = controller()->memcache->get_database_scalar("wallet_user_shares-$user->id-$algo",
		"select sum(difficulty) from shares where valid and algo=:algo and userid=$user->id", array(':algo'=>$algo));
	if(!$user_shares && !$minercount) continue;

	$total_shares = controller()->memcache->get_database_scalar("wallet_total_shares-$algo",
		"select sum(difficulty) from shares where valid and algo=:algo", array(':algo'=>$algo));
	if(!$total_shares) continue;

	$percent_shares = round($user_shares * 100 / $total_shares, 4);

	echo "<tr class='ssrow'>";
	echo "<td><b>$algo</b></td>";
	echo "<td align=right>$minercount</td>";
	echo "<td align=right width=100>{$percent_shares}%</td>";
	echo "<td align=right width=100><b>$user_rate1</b></td>";
	echo "<td align=right>$percent_bad</td>";
	echo "</tr>";
}

echo "</table>";

////////////////////////////////////////////////////////////////////////////////

$workers = getdbolist('db_workers', "userid=$user->id order by password");
if(count($workers))
{
	echo "<br>";
	echo "<table  class='dataGrid2'>";
	echo "<thead>";
	echo "<tr>";
	echo "<th align=left>Details</th>";
	echo "<th align=left>Extra</th>";
	echo "<th align=left>Algo</th>";
	echo "<th align=right>Diff</th>";
	echo "<th align=right title='extranonce.subscribe'>ES**</th>";
	echo "<th align=right width=80>Hashrate*</th>";
	echo "<th align=right width=60>Reject*</th>";
	echo "</tr>";
	echo "</thead>";

	foreach($workers as $worker)
	{
		$user_rate1 = yaamp_worker_rate($worker->id, $worker->algo);
		$user_rate1_bad = yaamp_worker_rate_bad($worker->id, $worker->algo);

		$percent = ($user_rate1 + $user_rate1_bad)? $user_rate1_bad * 100 / ($user_rate1 + $user_rate1_bad): 0;
		$percent = $percent? round($percent, 1).'%': '';

		$user_rate1 = $user_rate1? Itoa2($user_rate1).'h/s': '';

		$version = substr($worker->version, 0, 16);
		$password = substr($worker->password, 0, 16);

		$subscribe = Booltoa($worker->subscribe);

		echo "<tr class='ssrow'>";
		echo "<td title='$worker->version'>$version</td>";
		echo "<td title='$worker->password'>$password</td>";
		echo "<td>$worker->algo</td>";
		echo "<td align=right>$worker->difficulty</td>";
		echo "<td align=right>$subscribe</td>";
		echo "<td align=right>$user_rate1</td>";
		echo "<td align=right>$percent</td>";
		echo "</tr>";
	}

	echo "</table>";
}

echo "</div>";

echo "<p style='font-size: .8em'>
		&nbsp;* approximate from the last 5 minutes submitted shares<br>
		&nbsp;** extranonce.subscribe<br>
		</p>";

echo "</div><br>";








