<?
	if($this->_CFG['modulprm'][$this->_cl]['ver'] == '0.1') {
		$result = $this->SQL->execSQL('UPDATE users SET reg_ip = INET_ATON(reg_ip)');
		$result = $this->SQL->execSQL('alter table `users` drop column `mf_ipcreate`, drop column `mf_timeup`, drop column `mf_timecr`, change `reg_date` `mf_timecr` int(11) NOT NULL, change `reg_ip` `mf_ipcreate` bigint(20) NOT NULL, change `up_date` `mf_timeup` varchar(254) NOT NULL');
		$result = $this->SQL->execSQL('UPDATE users SET mf_timeup = UNIX_TIMESTAMP(mf_timeup)');
		$result = $this->SQL->execSQL('alter table `users` change `mf_timeup` `mf_timeup` int(12) NOT NULL');
	}

