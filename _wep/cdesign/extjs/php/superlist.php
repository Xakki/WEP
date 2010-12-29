<?php

function tpl_superlist(&$data)
{
	if (isset($data['_view']) && $data['_view'] == 'listcol')
	{

		$cols = array();

		$tdflag = 0;
		foreach($data['data']['thitem'] as $k=>$r) {
			if(!$tdflag) {
				if(isset($r['onetd'])){
					$tdflag = 1;
					$r['value'] = $r['onetd'];
				}	
				$cols[] = array(
					'header' => $r['value'],
					'dataIndex' => $k,
					'width' => 200,
				);
			}
			if($r['onetd']=='close') $tdflag = 0;
		}	

		return json_encode($cols); 


		$cols = array(
			array(
				'header' => 'Task',
				'dataIndex' => 'task',
				'width' => 230,
			),
			array(
				'header' => 'Duration',
				'width' => 100,
				'dataIndex' => 'duration',
	//			'align' => 'center',
	//			'sortType' => 'asFloat',
			),
			array(
				'header' => 'Assigned To',
				'width' => 150,
				'dataIndex' => 'user',
			)
		);

		return json_encode($cols);
	}


	$output = array();
	if(count($data['data']['item']))
	{
		$i = 0;
		foreach($data['data']['item'] as $k=>$r) {
			$tdflag = 0; 		
			
			foreach($r['tditem'] as $ktd=>$tditem) {
				if(!$tdflag) {
					if(isset($tditem['onetd'])) $tdflag = 1;

					$output[$i][$ktd] = $tditem['value'];
				}	 
				if(isset($tditem['onetd']) and $tditem['onetd']=='close')
					$tdflag = 0;
			}
			$i++;
		}
	}

//	print_r($data['data']['item']);

	return json_encode($output);
	
	$data = array(
						array(
							'task' => 'Kitchen supplies',
							'duration' => 0.25,
							'user' => 'Tommy Maintz',
							'leaf' => true,
							'iconCls' => 'task'
						),
						array(
							'task' => 'Groceries',
							'duration' => '.4',
							'user' => 'Tommy Maintz',
							'leaf' => true,
							'iconCls' => 'task'
						)
			
	);

	return json_encode($data);
	
	
	$json = "
		[{
			 task:'Project: Shopping',
			 duration:13.25,
			 user:'Tommy Maintz',
			 iconCls:'task-folder',
			 expanded: true,
			 children:[{
				  task:'Housewares',
				  duration:1.25,
				  user:'Tommy Maintz',
				  iconCls:'task-folder',
				  children:[{
						task:'Kitchen supplies',
						duration:0.25,
						user:'Tommy Maintz',
						leaf:true,
						iconCls:'task'
				  },{
						task:'Groceries',
						duration:.4,
						user:'Tommy Maintz',
						leaf:true,
						iconCls:'task'
				  },{
						task:'Cleaning supplies',
						duration:.4,
						user:'Tommy Maintz',
						leaf:true,
						iconCls:'task'
				  },{
						task: 'Office supplies',
						duration: .2,
						user: 'Tommy Maintz',
						leaf: true,
						iconCls: 'task'
				  }]
			 }, {
				  task:'Remodeling',
				  duration:12,
				  user:'Tommy Maintz',
				  iconCls:'task-folder',
				  expanded: true,
				  children:[{
						task:'Retile kitchen',
						duration:6.5,
						user:'Tommy Maintz',
						leaf:true,
						iconCls:'task'
				  },{
						task:'Paint bedroom',
						duration: 2.75,
						user:'Tommy Maintz',
						iconCls:'task-folder',
						children: [{
							 task: 'Ceiling',
							 duration: 1.25,
							 user: 'Tommy Maintz',
							 iconCls: 'task',
							 leaf: true
						}, {
							 task: 'Walls',
							 duration: 1.5,
							 user: 'Tommy Maintz',
							 iconCls: 'task',
							 leaf: true
						}]
				  },{
						task:'Decorate living room',
						duration:2.75,
						user:'Tommy Maintz',
						leaf:true,
						iconCls:'task'
				  },{
						task: 'Fix lights',
						duration: .75,
						user: 'Tommy Maintz',
						leaf: true,
						iconCls: 'task'
				  }, {
						task: 'Reattach screen door',
						duration: 2,
						user: 'Tommy Maintz',
						leaf: true,
						iconCls: 'task'
				  }]
			 }]
		},{
			 task:'Project: Testing',
			 duration:2,
			 user:'Core Team',
			 iconCls:'task-folder',
			 children:[{
				  task: 'Mac OSX',
				  duration: 0.75,
				  user: 'Tommy Maintz',
				  iconCls: 'task-folder',
				  children: [{
						task: 'FireFox',
						duration: 0.25,
						user: 'Tommy Maintz',
						iconCls: 'task',
						leaf: true
				  }, {
						task: 'Safari',
						duration: 0.25,
						user: 'Tommy Maintz',
						iconCls: 'task',
						leaf: true
				  }, {
						task: 'Chrome',
						duration: 0.25,
						user: 'Tommy Maintz',
						iconCls: 'task',
						leaf: true
				  }]
			 },{
				  task: 'Windows',
				  duration: 3.75,
				  user: 'Darrell Meyer',
				  iconCls: 'task-folder',
				  children: [{
						task: 'FireFox',
						duration: 0.25,
						user: 'Darrell Meyer',
						iconCls: 'task',
						leaf: true
				  }, {
						task: 'Safari',
						duration: 0.25,
						user: 'Darrell Meyer',
						iconCls: 'task',
						leaf: true
				  }, {
						task: 'Chrome',
						duration: 0.25,
						user: 'Darrell Meyer',
						iconCls: 'task',
						leaf: true
				  },{
						task: 'Internet Exploder',
						duration: 3,
						user: 'Darrell Meyer',
						iconCls: 'task',
						leaf: true
				  }]
			 },{
				  task: 'Linux',
				  duration: 0.5,
				  user: 'Aaron Conran',
				  iconCls: 'task',
				  children: [{
						task: 'FireFox',
						duration: 0.25,
						user: 'Aaron Conran',
						iconCls: 'task',
						leaf: true
				  }, {
						task: 'Chrome',
						duration: 0.25,
						user: 'Aaron Conran',
						iconCls: 'task',
						leaf: true
				  }]
			 }]
		}]";

	return $json;
}
