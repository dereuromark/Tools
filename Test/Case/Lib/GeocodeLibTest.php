<?php

App::uses('GeocodeLib', 'Tools.Lib');
App::uses('MyCakeTestCase', 'Tools.TestSuite');

# google maps
Configure::write('Google', array(
	'key' => 'ABQIAAAAk-aSeht5vBRyVc9CjdBKLRRnhS8GMCOqu88EXp1O-QqtMSdzHhQM4y1gkHFQdUvwiZgZ6jaKlW40kw',	//local
	'api' => '2.x',
	'zoom' => 16,
	'lat' => null,
	'lng' => null,
	'type' => 'G_NORMAL_MAP'
));

class GeocodeLibTest extends MyCakeTestCase {


	public $apiMockupReverseGeocode40206 = array(
		'reverseGeocode' => array(
			'lat' => '38.2643',
			'lng' => '-85.6999',
			'params' => array(
				'address' => '40206',
				'latlng' => '',
				'region' => '',
				'language' => 'en',
				'bounds' => '',
				'sensor' => 'false',
				'key' => 'AIzaSyAcQWSeMp_RF9W2_g2vOfLlUNCieHtHfFA',
				'result_type' => 'sublocality'
			)
		),
		'_fetch' => 'https://maps.googleapis.com/maps/api/geocode/json?address=40206&latlng=38.2643%2C-85.6999&language=en&sensor=false',
		'raw' => '{
			"results" : [
				{
					"address_components" : [
						{ "long_name" : "40206", "short_name" : "40206", "types" : [ "postal_code" ] },
						{ "long_name" : "Louisville", "short_name" : "Louisville", "types" : [ "locality", "political" ] },
						{ "long_name" : "Kentucky", "short_name" : "KY", "types" : [ "administrative_area_level_1", "political" ] },
						{ "long_name" : "United States", "short_name" : "US", "types" : [ "country", "political" ] }
					],
					"formatted_address" : "Louisville, KY 40206, USA",
					"geometry" : {
						"bounds" : {
							"northeast" : { "lat" : 38.2852558, "lng" : -85.664309 },
							"southwest" : { "lat" : 38.2395658, "lng" : -85.744801 }
						},
						"location" : { "lat" : 38.26435780000001, "lng" : -85.69997889999999 },
						"location_type" : "APPROXIMATE",
						"viewport" : {
							"northeast" : { "lat" : 38.2852558, "lng" : -85.664309 },
							"southwest" : { "lat" : 38.2395658, "lng" : -85.744801 }
						}
					},
					"types" : [ "postal_code" ]
				}
			],
			"status" : "OK"
		}',
	);


	public function setUp() {
		parent::setUp();

		$this->Geocode = new GeocodeLib();
	}

	public function tearDown() {
		parent::tearDown();
		unset($this->Geocode);
	}

	public function testObject() {
		$this->assertTrue(is_object($this->Geocode));
		$this->assertInstanceOf('GeocodeLib', $this->Geocode);
	}

	public function testDistance() {
		$coords = array(
			array('name' => 'MUC/Pforzheim (269km road, 2:33h)', 'x' => array('lat' => 48.1391, 'lng' => 11.5802), 'y' => array('lat' => 48.8934, 'lng' => 8.70492), 'd' => 228),
			array('name' => 'MUC/London (1142km road, 11:20h)', 'x' => array('lat' => 48.1391, 'lng' => 11.5802), 'y' => array('lat' => 51.508, 'lng' => -0.124688), 'd' => 919),
			array('name' => 'MUC/NewYork (--- road, ---h)', 'x' => array('lat' => 48.1391, 'lng' => 11.5802), 'y' => array('lat' => 40.700943, 'lng' => -73.853531), 'd' => 6479)
		);

		foreach ($coords as $coord) {
			$is = $this->Geocode->distance($coord['x'], $coord['y']);
			//echo $coord['name'].':';
			//pr('is: '.$is.' - expected: '.$coord['d']);
			$this->assertEquals($coord['d'], $is);
		}
	}

	public function testBlur() {
		$coords = array(
			array(48.1391, 1, 0.002), //'y'=>array('lat'=>48.8934, 'lng'=>8.70492), 'd'=>228),
			array(11.5802, 1, 0.002),
		);
		foreach ($coords as $coord) {
			$is = $this->Geocode->blur($coord[0], $coord[1]);
			//pr('is: '.$is.' - expected: '.$coord[0].' +- '.$coord[2]);
			$this->assertWithinMargin($is, $coord[0], $coord[2]);
			$this->assertNotWithinMargin($is, $coord[0], $coord[2] / 4);
		}
	}

	public function testConvert() {
		$values = array(
			array(3, 'M', 'K', 4.828032),
			array(3, 'K', 'M', 1.86411358),
			array(100000, 'I', 'K', 2.54),
		);
		foreach ($values as $value) {
			$is = $this->Geocode->convert($value[0], $value[1], $value[2]);
			//echo $value[0].$value[1].' in '.$value[2].':';
			//pr('is: '.returns($is).' - expected: '.$value[3]);
			$this->assertEquals($value[3], round($is, 8));
		}
	}

	public function testUrl() {
		$is = $this->Geocode->url();
		$this->assertFalse(empty($is));
		$this->assertPattern('#https://maps.googleapis.com/maps/api/geocode/(json|xml)\?.+#', $is);
	}

	// not possible with protected method

	public function _testFetch() {
		$url = 'http://maps.google.com/maps/api/geocode/xml?sensor=false&address=74523';
		$is = $this->Geocode->_fetch($url);
		//debug($is);

		$this->assertTrue(!empty($is) && substr($is, 0, 38) === '<?xml version="1.0" encoding="UTF-8"?>');

		$url = 'http://maps.google.com/maps/api/geocode/json?sensor=false&address=74523';
		$is = $this->Geocode->_fetch($url);
		//debug($is);
		$this->assertTrue(!empty($is) && substr($is, 0, 1) === '{');
	}

	public function testSetParams() {
	}

	public function testWithJson() {
		$this->Geocode->setOptions(array('output' => 'json'));
		$address = '74523 Deutschland';
		//echo '<h2>'.$address.'</h2>';
		$is = $this->Geocode->geocode($address);
		$this->assertTrue($is);

		$is = $this->Geocode->getResult();
		//debug($is);
		$this->assertTrue(!empty($is));
	}

	public function testSetOptions() {
		// should be the default
		$res = $this->Geocode->url();
		$this->assertTextContains('maps.googleapis.com', $res);

		$this->Geocode->setOptions(array('host' => 'maps.google.it'));
		// should now be ".it"
		$res = $this->Geocode->url();
		$this->assertTextContains('maps.google.it', $res);
	}

	public function testGeocode() {
		$address = '74523 Deutschland';
		//echo '<h2>'.$address.'</h2>';
		$is = $this->Geocode->geocode($address);
		//debug($is);
		$this->assertTrue($is);

		$is = $this->Geocode->getResult();
		//debug($is);
		$this->assertTrue(!empty($is));

		$is = $this->Geocode->error();
		//debug($is);
		$this->assertTrue(empty($is));

		$address = 'Leopoldstraße 100, München';
		//echo '<h2>'.$address.'</h2>';
		$is = $this->Geocode->geocode($address);
		//debug($is);
		$this->assertTrue($is);

		//pr($this->Geocode->debug());

		$is = $this->Geocode->getResult();
		//debug($is);
		$this->assertTrue(!empty($is));

		$is = $this->Geocode->error();
		//debug($is);
		$this->assertTrue(empty($is));

		$address = 'Oranienburger Straße 87, 10178 Berlin, Deutschland';
		//echo '<h2>'.$address.'</h2>';
		$is = $this->Geocode->geocode($address);
		//debug($is);
		$this->assertTrue($is);

		//pr($this->Geocode->debug());

		$is = $this->Geocode->getResult();
		//debug($is);
		$this->assertTrue(!empty($is));

		$is = $this->Geocode->error();
		//debug($is);
		$this->assertTrue(empty($is));
	}

	public function testGeocodeBadApiKey() {
		$address = 'Oranienburger Straße 87, 10178 Berlin, Deutschland';
		$is = $this->Geocode->geocode($address, array('sensor' => false, 'key' => 'testingBadApiKey'));
		$this->assertFalse($is);
		//pr($this->Geocode->debug());
		$is = $this->Geocode->error();
		$this->assertEqual('Error REQUEST_DENIED (The provided API key is invalid.)', $is);

	}

	public function testGeocodeInvalid() {
		$address = 'Hjfjosdfhosj, 78878 Mdfkufsdfk';
		//echo '<h2>'.$address.'</h2>';
		$is = $this->Geocode->geocode($address);
		//debug($is);
		$this->assertFalse($is);

		//pr($this->Geocode->debug());

		$is = $this->Geocode->error();
		//debug($is);
		$this->assertTrue(!empty($is));
	}

	public function testGetMaxAddress() {
		$this->assertEqual($this->Geocode->_getMaxAccuracy(array('street_address' => 'abc')), GeocodeLib::ACC_STREET);
		$this->assertEqual($this->Geocode->_getMaxAccuracy(array('intersection' => 'abc')), GeocodeLib::ACC_INTERSEC);
		$this->assertEqual($this->Geocode->_getMaxAccuracy(array('route' => 'abc')), GeocodeLib::ACC_ROUTE);
		$this->assertEqual($this->Geocode->_getMaxAccuracy(array('sublocality' => 'abc')), GeocodeLib::ACC_SUBLOC);
		$this->assertEqual($this->Geocode->_getMaxAccuracy(array('locality' => 'abc')), GeocodeLib::ACC_LOC);
		$this->assertEqual($this->Geocode->_getMaxAccuracy(array('postal_code' => 'abc')), GeocodeLib::ACC_POSTAL);
		$this->assertEqual($this->Geocode->_getMaxAccuracy(array('country' => 'aa')), GeocodeLib::ACC_COUNTRY);
		$this->assertEqual($this->Geocode->_getMaxAccuracy(array()), GeocodeLib::ACC_COUNTRY);
		// mixed
		$this->assertEqual($this->Geocode->_getMaxAccuracy(array(
			'country' => 'aa',
			'postal_code' => 'abc',
			'locality' => '',
			'street_address' => '',
		)), GeocodeLib::ACC_POSTAL);
	}

	public function testGeocodeMinAcc() {
		// address = postal_code, minimum = street level
		$address = 'Deutschland';
		$this->Geocode->setOptions(array('min_accuracy' => GeocodeLib::ACC_STREET));
		$is = $this->Geocode->geocode($address);
		$this->assertFalse($is);
		$is = $this->Geocode->error();
		$this->assertTrue(!empty($is));
	}

	public function testGeocodeInconclusive() {
		// seems like there is no inconclusive result anymore!!!
		$address = 'Neustadt';

		// allow_inconclusive = TRUE
		$this->Geocode->setOptions(array('allow_inconclusive' => true, 'min_accuracy' => GeocodeLib::ACC_POSTAL));
		$is = $this->Geocode->geocode($address);
		$this->assertTrue($is);
		$res = $this->Geocode->getResult();
		$this->assertTrue(count($res) > 4);

		$is = $this->Geocode->isInconclusive();
		$this->assertTrue($is);

		$this->Geocode->setOptions(array('allow_inconclusive' => false));
		$is = $this->Geocode->geocode($address);
		$this->assertFalse($is);
	}

	public function testReverseGeocode() {
		$coords = array(
			array(-34.594445, -58.37446, 'Calle Florida 1134-1200, Buenos Aires'),
			array(48.8934, 8.70492, 'B294, 75175 Pforzheim, Deutschland')
		);

		foreach ($coords as $coord) {
			$is = $this->Geocode->reverseGeocode($coord[0], $coord[1]);
			$this->assertTrue($is);

			$is = $this->Geocode->getResult();
			$this->assertTrue(!empty($is));
			//debug($is);
			$address = isset($is[0]) ? $is[0]['formatted_address'] : $is['formatted_address'];
			$this->assertTextContains($coord[2], $address);
		}
	}

	public function test_transformData() {
		// non-full records
		$data = array('record' => 'OK');
		$this->assertEqual($this->Geocode->_transformData($data), $data);
		$data = array();
		$this->assertEqual($this->Geocode->_transformData($data), $data);
		$data = '';
		$this->assertEqual($this->Geocode->_transformData($data), $data);
		$data = 'abc';
		$this->assertEqual($this->Geocode->_transformData($data), $data);

		// full record
		$expect = array(
			'results' => array(
				0 => array (
					'formatted_address' => 'Louisville, KY 40206, USA',
					// organized location components
					'country' => 'United States',
					'country_code' => 'US',
					'country_province' => 'Kentucky',
					'country_province_code' => 'KY',
					'postal_code' => '40206',
					'locality' => 'Louisville',
					'sublocality' => '',
					'route' => '',
					// vetted "types"
					'types' => array (
						0 => 'postal_code',
					),
					// simple lat/lng
					'lat' => 38.264357800000013,
					'lng' => -85.699978899999991,
					'location_type' => 'APPROXIMATE',
					'viewport' => array (
						'sw' => array (
							'lat' => 38.239565800000001,
							'lng' => -85.744800999999995,
						),
						'ne' => array (
							'lat' => 38.285255800000002,
							'lng' => -85.664309000000003,
						),
					),
					'bounds' => array (
						'sw' => array (
							'lat' => 38.239565800000001,
							'lng' => -85.744800999999995,
						),
						'ne' => array (
							'lat' => 38.285255800000002,
							'lng' => -85.664309000000003,
						),
					),
					// injected static maxAccuracy
					'maxAccuracy' => 5,
				),
			),
			'status' => 'OK',
		);
		$data = json_decode($this->apiMockupReverseGeocode40206['raw'], true);
		$this->assertEqual($this->Geocode->_transformData($data), $expect);

		// multiple full records
		// TODO:...
	}

	public function testGetResult() {

	}

}
