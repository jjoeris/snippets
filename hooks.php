<?php

$dir = $_GET[ 'dir' ];
$file = $_GET[ 'file' ];
$line = $_GET[ 'line' ];

if ( empty( $dir ) && empty( $file ) && empty( $line ) )
    die();

    
$results = array();
function get_hooks( $dir ) { 
		global $results;
		$fp = opendir( $dir );
		
		while( $file = readdir( $fp ) ){
			if( $file == '.' || $file == '..' || substr( $file,0,1) == '.' )
			    continue; 
			$tmp_path = $dir . '/' . $file;
			if( is_dir( $tmp_path ) ) {
				// get recursivly
				get_hooks( $tmp_path );
			} else {
				$contents = file( $tmp_path );
				$results[ $tmp_path ]  = preg_grep( '/apply_filters|do_action/', $contents );
			}
		}
		closedir( $fp );
} 

if( !empty( $dir ) ) {
	get_hooks( $dir );
	
	echo '<ul>';
	foreach ( $results as $file => $hits ) {
		if( empty( $hits ) )
		    continue;
		printf( '<li>%s</li>', $file );
		echo '<ul>';
		foreach ( $hits as $line_number => $found ) {
			preg_match( '/(apply_filters|do_action)\s*\(\s*(\S*)\s*/',$found , $matches );
			$hookname = str_replace( array('\'', '"', ","), array( '', '', '' ), $matches[2] );
		    printf( '<li><b>%s</b>, <i>Line %d</i> <a href="hooks.php?file=%s&line=%d#line%d" target="_blank">code</a></li>', 
		     $hookname, $line_number+1, urlencode( $file ), $line_number+1, $line_number+1 );	
		}
		echo '</ul>';
	}
	echo '</ul>';
		
} else if ( !empty( $file ) && !empty( $line ) ) {
	renderFile( $file, $line );
}

   function renderFile($filename, $highlight) { 
        if(file_exists($filename) && is_file($filename)) { 
            $code = highlight_file($filename, true); 
            $counter = 1; 
            $arr = explode('<br />', $code); 
            echo '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-family: monospace;">' . "\r\n"; 
            foreach($arr as $line) { 
                echo '<tr>' . "\r\n"; 
                    echo '<td width="65px" nowrap style="color: #666;"><a name="line' . $counter. '">' . $counter . '</a>:</td>' . "\r\n"; 

                    // fix multi-line comment bug 
                    if((strstr($line, '<span style="color: #FF8000">/*') !== false) && (strstr($line, '*/') !== false)) { // single line comment using /* */ 
                        $comments = false; 
                        $startcolor = "orange"; 
                    }   
                    elseif(strstr($line, '<span style="color: #FF8000">/*') !== false) { // multi line comment using /* */ 
                        $startcolor = "orange"; 
                        $comments = true; 
                    }   
                    else { // no comment marks found 
                        $startcolor = "green"; 
                        if($comments) { // continuation of multi line comment 
                            if(strstr($line, '*/') !== false) { 
                                $comments = false; 
                                $startcolor = "orange"; 
                            }   
                            else { 
                                $comments = true; 
                            }   
                        }   
                        else { // normal line   
                            $comments = false; 
                            $startcolor = "green"; 
                        }   
                    }   
                    // end fix multi-line comment bug 
					if( $counter == $highlight)
						$bgcolor = ' background: yellow;';
					else 
						$bgcolor = '';
                    if($comments) 
                        echo '<td width="100%" nowrap style="color: orange;' .$bgcolor. '">' . $line . '</td>' . "\r\n"; 
                    else 
                        echo '<td width="100%" nowrap style="color: ' . $startcolor . ';' .$bgcolor. '">' . $line . '</td>' . "\r\n"; 

                    echo '</tr>' . "\r\n"; 
                    $counter++; 
            }   
            echo '</table>' . "\r\n"; 
        }   
        else { 
            echo "<p>The file <i>$filename</i> could not be opened.</p>\r\n"; 
            return; 
        }   
    } 
?>