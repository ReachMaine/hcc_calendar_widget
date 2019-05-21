<?php
/* functions to support hcc_calendar pluging*/
/* mods: 
*	14Oct2014 zig - use >= today instead of > today.
*
*/
	function hcc_get_lastest($blogid, $limit, $cats, $towns, $tags) {
			global $wpdb;
			if ($towns) { // turn list of towns to from 'town1, town2, town3' to 'town1', 'town2', 'town3'
				$str_towns = str_replace(", ", ",", $towns);
				$str_towns = "'".implode("','", explode(",", $str_towns))."'";
			}
			if ($tags) {
				$str_tags = str_replace(", ", ",", $tags);
				$str_tags = "'".implode("','", explode(",", $str_tags))."'";
			}
			//echo $str_towns;
			$startDate = date_format( new DateTime(), 'Ymd');
			$calquery = "SELECT p.ID,p.post_title,";
    
		    $calquery .= " (SELECT  CAST( meta_value AS DATE) FROM `ea_12_postmeta` pm WHERE p.ID=pm.post_id and pm.meta_key='_EventStartDate') as 'startDate',  ";
		    $calquery .= " (SELECT  CAST( meta_value AS TIME) FROM `ea_12_postmeta` pm WHERE p.ID=pm.post_id and pm.meta_key='_EventStartDate') as 'startTime',  ";
		    $calquery .= " (SELECT CAST( meta_value AS DATE) FROM `ea_12_postmeta` pm WHERE p.ID=pm.post_id and pm.meta_key='_EventEndDate') as 'endDate', ";
		    $calquery .= " (SELECT CAST( meta_value AS TIME) FROM `ea_12_postmeta` pm WHERE p.ID=pm.post_id and pm.meta_key='_EventEndDate') as 'endTime', ";
			$calquery .= " (SELECT  meta_value FROM `ea_12_postmeta` pm WHERE p.ID=pm.post_id and pm.meta_key='_EventCost') as 'cost', ";
			$calquery .= " vp.post_title as 'Venue', ";
    		$calquery .= " (SELECT  meta_value FROM `ea_12_postmeta` pm WHERE p.ID=pm.post_id and pm.meta_key='_EventVenueID') as 'VenueID', ";	
    		$calquery .= " (SELECT  meta_value FROM `ea_12_postmeta` pm WHERE vp.ID=pm.post_id and pm.meta_key='_VenueAddress') as 'Address', ";
    		$calquery .= " (SELECT  meta_value FROM `ea_12_postmeta` pm WHERE vp.ID=pm.post_id and pm.meta_key='_VenueCity') as 'City',  ";	
    		$calquery .= " (SELECT  meta_value FROM `ea_12_postmeta` pm WHERE vp.ID=pm.post_id and pm.meta_key='_VenuePhone') as 'Phone', ";	
    		$calquery .= " (SELECT  meta_value FROM `ea_12_postmeta` pm WHERE vp.ID=pm.post_id and pm.meta_key='_VenueURL') as 'website', ";	
    		/* $calquery .= "  ";	 */
    		$calquery .= " p.post_content "; 
    		/* now for the tables & joins */
			$calquery .= " FROM `ea_12_posts` p";
			$calquery .= " JOIN `ea_12_postmeta` pm ON p.ID=pm.post_id and pm.meta_key = '_EventVenueID' ";
			$calquery .= " LEFT JOIN `ea_12_posts` vp ON  pm.meta_value = vp.ID  and pm.meta_key= '_EventVenueID' ";
			if ($cats) {
				$calquery .= "  LEFT JOIN ea_12_term_relationships tr ON (p.ID = tr.object_id) ";
				$calquery .= " LEFT JOIN ea_12_term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) ";
			}
			if ($tags) {
				$calquery .= " LEFT JOIN ea_12_term_relationships ON (p.ID = ea_12_term_relationships.object_id) ";
				$calquery .= " INNER JOIN ea_12_term_taxonomy ON (ea_12_term_relationships.term_taxonomy_id = ea_12_term_taxonomy.term_taxonomy_id) ";
				$calquery .= " INNER JOIN ea_12_terms ON (ea_12_terms.term_id = ea_12_term_taxonomy.term_id) ";
			}
			/* the where clause */
			$calquery .= " WHERE p.post_status = 'publish'  AND p.post_type = 'tribe_events'"; 
			/* date part of where clause event start >= today   */
			$calquery .=" AND ((select cast(meta_value AS Date) from `ea_12_postmeta` where post_id=p.ID and meta_key= '_EventStartDate') >= cast('".$startDate."' as Date) ";
						/* or the startdate is today or before AND the end date is >= today*/
    		$calquery .= "  OR (select cast(meta_value AS Date) from `ea_12_postmeta` where post_id=p.ID and meta_key= '_EventStartDate') <= cast('".$startDate."'as Date) ";
        	$calquery .= "  AND (select cast(meta_value AS Date) from `ea_12_postmeta` where post_id=p.ID and meta_key= '_EventEndDate') >= cast('".$startDate."' as Date) " ; 
       		$calquery .= ")";
			if ($towns || $tags) {
				$calquery .= "AND ( ("; 
				if ($towns) {
					$calquery .= "SELECT  meta_value FROM `ea_12_postmeta` pm WHERE vp.ID=pm.post_id and pm.meta_key='_VenueCity') in (".$str_towns.")";
				} else {
					$calquery .= " 1 ";
				}
				$calquery .= ") OR (";
				if ($tags) {
					$calquery .= "AND ea_12_term_taxonomy.taxonomy = 'post_tag' AND ea_12_terms.slug in (".$str_towns.") ";
				} else {
					$calquery .= " 1 ";
				}
				$calquery .= ") ) ";
			}
			
			if ($cats ) {
				$calquery .= " AND tt.taxonomy = 'tribe_events_cat' ";
   				$calquery .= "AND tt.term_id in (".$cats.')';
			}
			$calquery .= " ORDER BY startDate ASC, City ASC";
			$calquery .= " LIMIT ".$limit;
			//echo $calquery; 
			$calresult = $wpdb->get_results($calquery); 
			return $calresult;
			/* var_dump($calresult); */
		
	} /* end get_lastest */
?>