<?php

class qa_xml_download_page {

	function match_request($request) {
		$parts = explode('/', $request);
		return $parts[0]=='xml-download';
	}

	 // for display in admin interface under admin/pages
    function suggest_requests()
    {        
            return array(
                    array(
                        'title' => 'XML Download', // title of page
                        'request' => '?qa=xml-download', // request name
                        'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
                    ),
            );
    }

	function process_request($request) {
		$qa_content = qa_content_prepare();
		$qa_content['title'] = 'XML Download';

		require_once QA_INCLUDE_DIR.'qa-db-metas.php';

		if (qa_clicked('generateXML')) {
			// do sql database download as xml
			qa_xml_generator::generate();
			$qa_content['form_content']['ok']="<a href='./qa-plugin/xml-download/Q2A_backup.xml'>Document</a>";
		}

		$qa_content['form']=array(
			'tags' => 'METHOD="POST" ACTION="'.qa_self_html().'"',
			'style' => 'tall',
			'buttons' => array(
				array(
					'tags' => 'NAME="generateXML"',
					'label' => 'Generate XML',
				),
			),
		);

		$qa_content['focusid'] = 'generateXML';		// focus on the download button

		return $qa_content;
	}

}

class qa_xml_generator {
	public static function generate() {
		require_once QA_INCLUDE_DIR.'qa-app-posts.php';
		require_once QA_INCLUDE_DIR.'qa-app-users.php';

		$posts = qa_db_query_raw("SELECT title, content, categoryid from qa_posts where type='Q'");
		$posts = qa_db_read_all_assoc($posts);

		// set up doc
		$filePath = realpath("./qa-plugin/xml-download/Q2A_backup.xml");
		$newDoc = new DOMDocument();

		$comment = $newDoc->createComment("This is a backup from ".date(DATE_RFC2822));
		$comment = $newDoc->appendChild($comment);

		$root = $newDoc->createElement("questions");
		$root = $newDoc->appendChild($root);

		foreach($posts as $p) {
			$contentText = $p["content"];
			$titleText = $p["title"];
			$categoryid = $p["categoryid"];
			$categoryText = mysql_result(qa_db_query_raw("SELECT title from qa_categories where categoryid='".$categoryid."'"),0);

			$post = $newDoc->createElement('question'); //create post element and append to root
	        $post = $root->appendChild($post);

	        $title = $newDoc->createElement('title'); //create title element and append to post
	        $title = $post->appendChild($title);
	        $text = $newDoc->createTextNode($titleText); //create text node for title and append to title
	        $text = $title->appendChild($text);

	        $content = $newDoc->createElement('content'); //create content element and appand to post
	        $content = $post->appendChild($content);
	        $text = $newDoc->createCDATASection($contentText); //create cdata section for content and append to content
	        $text = $content->appendChild($text);

	        $category = $newDoc->createElement('category'); //create category element and appand to post
	        $category = $post->appendChild($category);
	        $text = $newDoc->createTextNode($categoryText); //create cdata section for category and append to category
	        $text = $category->appendChild($text);
		}
		$newDoc->save($filePath);
	}
}

class qa_xml_downloader {
	public static function download() {
		$file = './qa-plugin/xml-download/Q2A_backup.xml';
		if (file_exists($file)) {
		    header('Content-Description: File Transfer');
		    header('Content-Type: application/octet-stream');
		    header('Content-Disposition: attachment; filename='.basename($file));
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize($file));
		    ob_clean();
		    flush();
		    readfile($file);
		    exit;
		}
	}
}