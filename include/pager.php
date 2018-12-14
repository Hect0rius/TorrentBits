<?php
/*
+------------------------------------------------
|   TBDev.net BitTorrent Tracker PHP
|   =============================================
|   by CoLdFuSiOn
|   (c) 2003 - 2009 TBDev.Net
|   http://www.tbdev.net
|   =============================================
|   svn: http://sourceforge.net/projects/tbdevnet/
|   Licence Info: GPL
+------------------------------------------------
|   $Date$
|   $Revision$
|   $Author$
|   $URL$
+------------------------------------------------
*/


function pager($data)
	{
		
		$pager = array( 'pages' => 0, 'page_span' => '', 'start' => '', 'end' => '' );
		
		$section = $data['span'] = isset($data['span']) ? $data['span'] : 2;
		
		$parameter  = isset($data['parameter'])	? $data['parameter']	 : 'page';
		
		$mini = isset($data['mini']) ? 'mini' : '';
		
		
		if ( $data['count'] > 0 )
		{
			$pager['pages'] = ceil( $data['count'] / $data['perpage'] );
		}
		
		$pager['pages'] = $pager['pages'] ? $pager['pages'] : 1;
		
		
		$pager['total_page']   = $pager['pages'];
		$pager['current_page'] = $data['start_value'] > 0 ? ($data['start_value'] / $data['perpage']) + 1 : 1;
		
		
		$previous_link = "";
		$next_link     = "";
		
		if ( $pager['current_page'] > 1 )
		{
			$start = $data['start_value'] - $data['perpage'];
			$previous_link = "<a href='{$data['url']}&amp;$parameter=$start' title='Previous'><span class='{$mini}pagelink'>&lt;</span></a>";
		}
		
		if ( $pager['current_page'] < $pager['pages'] )
		{
			$start = $data['start_value'] + $data['perpage'];
			$next_link = "&nbsp;<a href='{$data['url']}&amp;$parameter=$start' title='Next'><span class='{$mini}pagelink'>&gt;</span></a>";
		}
		

		
		if ($pager['pages'] > 1)
		{
			if ( isset($data['mini']) )
			{
        $pager['first_page'] = "<img src='".F_IMAGES."/multipage.gif' alt='' title='' />";
			}
			else
			{
        $pager['first_page'] = "<span style='background: #F0F5FA; border: 1px solid #072A66;padding: 1px 3px 1px 3px;'>{$pager['pages']} Pages</span>&nbsp;";
			}
			
			for( $i = 0; $i <= $pager['pages'] - 1; ++$i )
			{
				$RealNo = $i * $data['perpage'];
				$PageNo = $i+1;
				
				if ($RealNo == $data['start_value'])
				{
					$pager['page_span'] .= $mini ? "&nbsp;<a href='{$data['url']}&amp;$parameter={$RealNo}' title='$PageNo'><span  class='{$mini}pagelink'>$PageNo</span></a>" : "&nbsp;<span class='pagecurrent'>{$PageNo}</span>";
				}
				else
				{
					if ($PageNo < ($pager['current_page'] - $section))
					{
						$pager['start'] = "<a href='{$data['url']}' title='Goto First'><span class='{$mini}pagelinklast'>&laquo;</span></a>&nbsp;";
						continue;
					}
					
					
					if ($PageNo > ($pager['current_page'] + $section))
					{
						$pager['end'] = "&nbsp;<a href='{$data['url']}&amp;$parameter=".(($pager['pages']-1) * $data['perpage'])."' title='Go To Last'><span class='{$mini}pagelinklast'>&raquo;</span></a>&nbsp;";
						break;
					}
					
					
					$pager['page_span'] .= "&nbsp;<a href='{$data['url']}&amp;$parameter={$RealNo}' title='$PageNo'><span  class='{$mini}pagelink'>$PageNo</span></a>";
				}
			}
			
			$pager['return'] = "<div style='float:left;'>{$pager['first_page']}{$pager['start']}{$previous_link}{$pager['page_span']}{$next_link}{$pager['end']}
			</div>";
			
		}
		else
		{
			$pager['return']    = '';
		}
	
		return $pager['return'];
	}

?>