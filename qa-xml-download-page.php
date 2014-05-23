<?php

class qa_xml_download_page {

	function match_request($request) {
		$parts = explode('/', $request);
		return $parts[0]=='xml-download';
	}

	function process_request($request) {
		$qa_content = qa_content_prepare();
		$qa_content['title'] = 'XML Download';

		require_once QA_INCLUDE_DIR.'qa-db-metas.php';

		if (qa_clicked('downloadXML')) {
			// do sql database download as xml
		}

		$qa_content['form']=array(
			'tags' => 'METHOD="POST" ACTION="'.qa_self_html().'"',
			'style' => 'tall',
			'buttons' => array(
				array(
					'tags' => 'NAME="downloadXML"',
					'label' => 'Download XML',
				),
			),
		);

		$qa_content['focusid'] = 'downloadXML';		// focus on the download button

		return $qa_content;
	}

}
