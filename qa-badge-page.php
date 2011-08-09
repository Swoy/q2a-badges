<?php

	class qa_badge_page {
		
		var $directory;
		var $urltoroot;
		
		function load_module($directory, $urltoroot)
		{
			$this->directory=$directory;
			$this->urltoroot=$urltoroot;
		}
		
		function suggest_requests() // for display in admin interface
		{	
			return array(
				array(
					'title' => qa_badge_lang('badges/badges'),
					'request' => 'badges',
					'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				),
			);
		}
		
		function match_request($request)
		{
			if ($request=='badges')
				return true;

			return false;
		}
		
		function process_request($request)
		{
			$qa_content=qa_content_prepare();

			$qa_content['title']=qa_badge_lang('badges/badge_list_title');

			$badges = qa_get_badge_list();
			
			$totalawarded = 0;
			
			$qa_content['custom']='<em>'.qa_badge_lang('badges/badge_list_pre').'</em><br />';
			$qa_content['custom2']='<table cellspacing="20">';
			$c = 2;
			
			$result = qa_db_read_all_assoc(
				qa_db_query_sub(
					'SELECT COUNT(id),badge_slug  FROM ^userbadges GROUP BY badge_slug'
				)
			);
			
			foreach($result as $r) {
				if($r['COUNT(id)'] > 0) $count[$r['badge_slug']] = $r['COUNT(id)'];
				$totalawarded = (int)$totalawarded+(int)$r['COUNT(id)'];
			}
			
			foreach($badges as $slug => $info) {
				if(qa_opt('badge_'.$slug.'_enabled') == '0') continue;
				if(!qa_opt('badge_'.$slug.'_name')) qa_opt('badge_'.$slug.'_name',qa_badge_lang('badges/'.$slug));
				$name = qa_opt('badge_'.$slug.'_name');
				$var = qa_opt('badge_'.$slug.'_var');
				$desc = qa_badge_desc_replace($slug,$var,$name);
				$type = qa_get_badge_type($info['type']);
				$types = $type['slug']; 
				$typen = $type['name']; 
				$qa_content['custom'.++$c]="<tr class='badge-entry'><td class='badge-name'><span class='badge-$types' title='".$typen."'>$name</span></td><td class='badge-desc'>$desc</td>".(isset($count[$slug]) ? "<td class='badge-count'>".$count[$slug]." ".qa_badge_lang('badges/awarded')."</td>":"<td></td>")."</tr>";
			
			}
			$qa_content['custom'.++$c]="<tr class='badge-entry'><td class='total-badges'>".count($badges)." ".qa_badge_lang('badges/badges_total')."</td><td></td>".($totalawarded > 0 ? "<td class='total-badge-count'>".$totalawarded." ".qa_badge_lang('badges/awarded_total')."</td>":"<td></td>")."</tr>";

			$qa_content['custom'.++$c]='</table>';
			if(isset($qa_content['navigation']['main']['custom-2'])) $qa_content['navigation']['main']['custom-2']['selected'] = true;

			return $qa_content;
		}
	
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
