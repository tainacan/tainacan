<?php

namespace Tainacan\Mappers;

/**
 * Support Dublin Core Mapping 
 * http://purl.org/dc/elements/1.1/
 *
 */
class Wiki_Data extends Mapper {
	public $slug = 'wiki-data';
	public $name = 'WikiData';
	public $description = 'The model mapper using the WikiData source';
	public $allow_extra_metadata = true;
	public $context_url = 'http://??????';
	public $header = '??????';
	public $prefixes = [
		'dc' => 'http://purl.org/dc/elements/1.1/'
	];
	public $metadata = [];	
	public $add_meta_form = '';
	
	public function __construct() {
		parent::__construct();
		$this->init_filters();
		$this->add_meta_form = $this->create_meta_form();
	}

	public function init_filters() {
		add_filter( 'tainacan-metadatum-get-metadata-type-object', function ( $object_type, $metadatum ) {
			$exposer_mapping = $metadatum->get_exposer_mapping();
			if( isset($exposer_mapping[$this->slug]) && !empty($exposer_mapping[$this->slug]) ) {
				$object_type->set_component('tainacan-wikidata');
			}
			return $object_type;
		}, 10, 2 );
	}

	public function create_meta_form() {
		ob_start();
		?>

		<style>
			.autocomplete {
				position: relative;
			}
			.autocomplete-items {
				position: absolute;
				border: 1px solid #d4d4d4;
				border-bottom: none;
				border-top: none;
				z-index: 99;
				/*position the autocomplete items to be the same width as the container:*/
				top: 100%;
				left: 0;
				right: 0;
			}
			.autocomplete-items div {
				font-size: 0.875em;
				padding: 8px 12px;
				cursor: pointer;
				background-color: #fff;
				border-bottom: 1px solid #d4d4d4;
				display: flex;
				justify-content: space-between;
				align-items: baseline;
			}
			.autocomplete-items div > span {
				font-size: 0.75em;
				opacity: 0.85;
				margin-left: auto;
			}
			.autocomplete-items div:hover {
				/*when hovering an item:*/
				background-color: #e9e9e9;
			}
			.autocomplete-active {
				/*when navigating through the items using the arrow keys:*/
				background-color: DodgerBlue !important;
				color: #ffffff;
			}
		</style>
		
		<div class="field">
			<label class="label is-inline">Wikidata Property</label>
			<div class="control is-clearfix autocomplete">
				<input
					onfocus="
						function mountWikidataAutocomplete(inputField) {

							if ( !inputField ) return;

							if ( inputField.classList.contains('mounted') ) return;

							inputField.classList.add('mounted');

							let currentFocus;

							async function fetchFromWikidata(searchValue)  {

								try {
									const requestHeaders = new Headers({
										'Content-Type': 'application/json'
									});
									const url = 'https://www.wikidata.org/w/api.php?origin=*&action=wbsearchentities&format=json&language=pt-br&uselang=pt-br&type=property&search=' + searchValue;
									const request = new Request(url, requestHeaders);
									const res = await fetch(url)
									const data = await res.json();
									return data.search;
								} catch(e) {
									console.log(e, e.response)
									return [];
								}
							}


							/*execute a function when someone writes in the text field:*/
							inputField.addEventListener('input', function(e) {
								let a, b, i, searchValue = this.value;
								/*close any already open lists of autocompleted values*/
								closeAllLists();

								if (!searchValue) return false;

								fetchFromWikidata(searchValue).then((searchResults) => {
									currentFocus = -1;
								
									/*create a DIV element that will contain the items (values):*/
									a = document.createElement('DIV');
									a.setAttribute('id', this.id + 'autocomplete-list');
									a.setAttribute('class', 'autocomplete-items');
									/*append the DIV element as a child of the autocomplete container:*/
									this.parentNode.appendChild(a);
									/*for each item in the array...*/
									for (i = 0; i < searchResults.length; i++) {
										/*check if the item starts with the same letters as the text field value:*/
										if (searchResults[i].label.substr(0, searchValue.length).toUpperCase() == searchValue.toUpperCase()) {
											/*create a DIV element for each matching element:*/
											b = document.createElement('DIV');
											/*make the matching letters bold:*/
											b.innerHTML = '<strong>' + searchResults[i].label.substr(0, searchValue.length) + '</strong>';
											b.innerHTML += searchResults[i].label.substr(searchValue.length);
											b.innerHTML += '<span>' + searchResults[i].id + '</span>'
											/*insert a input field that will hold the current array item\'s value:*/
											b.innerHTML += `<input type='hidden' value='` + searchResults[i].id + `'>`;
											/*execute a function when someone clicks on the item value (DIV element):*/
												b.addEventListener('click', function(e) {
												/*insert the value for the autocomplete text field:*/
												inputField.value = this.getElementsByTagName('input')[0].value;
												/*close the list of autocompleted values,
												(or any other open lists of autocompleted values:*/
												closeAllLists();
											});
											a.appendChild(b);
										}
									}
									
								});
								
							});

							/*execute a function presses a key on the keyboard:*/
							inputField.addEventListener('keydown', function(e) {
								let x = document.getElementById(this.id + 'autocomplete-list');
								if (x) x = x.getElementsByTagName('div');
								if (e.keyCode == 40) {
									/*If the arrow DOWN key is pressed,
									increase the currentFocus variable:*/
									currentFocus++;
									/*and and make the current item more visible:*/
									addActive(x);
								} else if (e.keyCode == 38) { //up
									/*If the arrow UP key is pressed,
									decrease the currentFocus variable:*/
									currentFocus--;
									/*and and make the current item more visible:*/
									addActive(x);
								} else if (e.keyCode == 13) {
									/*If the ENTER key is pressed, prevent the form from being submitted,*/
									e.preventDefault();
									if (currentFocus > -1) {
										/*and simulate a click on the `active` item:*/
										if (x) x[currentFocus].click();
									}
								}
							});
							
							function addActive(x) {
								/*a function to classify an item as `active`:*/
								if (!x) return false;
								/*start by removing the `active` class on all items:*/
								removeActive(x);
								if (currentFocus >= x.length) currentFocus = 0;

								if (currentFocus < 0) currentFocus = (x.length - 1);
								/*add class `autocomplete-active`:*/
								x[currentFocus].classList.add('autocomplete-active');
							}
							function removeActive(x) {
								/*a function to remove the 'active' class from all autocomplete items:*/
								for (let i = 0; i < x.length; i++) {
									x[i].classList.remove('autocomplete-active');
								}
							}
							function closeAllLists(elmnt) {
								/*close all autocomplete lists in the document,
								except the one passed as an argument:*/
								let x = document.getElementsByClassName('autocomplete-items');
								for (let i = 0; i < x.length; i++) {
									if (elmnt != x[i] && elmnt != inputField) {
										x[i].parentNode.removeChild(x[i]);
									}
								}
							}

							/*execute a function when someone clicks in the document:*/
							document.addEventListener('click', function (e) {
								closeAllLists(e.target);
							});
						}
						mountWikidataAutocomplete(document.getElementById('wikidataPropertyIdInput'));
					"
					id="wikidataPropertyIdInput"
					class="input"
					type="text"
					placeholder="Insira uma propriedade da Wikidata"
					name="wikidataPropertyId">
			</div>
		</div>

		<?php

		return ob_get_clean();
	}
}