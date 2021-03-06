<?php
	
	requires(
		'/Model/EncodingProfile',
		'/Helper/EncodingProfile'
	);
	
	class Controller_EncodingProfiles extends Controller_Application {
		
		public $requireAuthorization = true;
		
		public function index() {
			$this->profiles = EncodingProfile::findAll()
				->scoped(['with_version_count'])
				->orderBy('slug');
			
			return $this->render('encoding/profiles/index');
		}
		
		public function view(array $arguments) {
			$this->profile = EncodingProfile::findOrThrow($arguments['id']);
			
			$this->form('encodingprofiles', 'compare', $this->profile);
			$this->versions = $this->profile->Versions->orderBy('revision DESC');
			
			return $this->render('encoding/profiles/view');
		}
		
		public function compare(array $arguments) {
			$this->profile = EncodingProfile::findOrThrow($arguments['id']);
			
			$values = $this->form()->getValues();
			
			if (!isset($values['version_a']) or !isset($values['version_b'])) {
				return $this->redirect('encodingprofiles', 'view', $this->profile);
			}
			
			if ($values['version_a'] == $values['version_b']) {
				$this->flash('Cannot compare a version with itself');
				return $this->redirect('encodingprofiles', 'view', $this->profile);
			}
			
			$versions = EncodingProfileVersion::findAll(array())
				/*->select('xml_template')*/
				->where(array('id' => array($values['version_a'], $values['version_b'])))
				->indexBy('id', 'xml_template')
				->toArray();
			
			$this->Response->setContentType('text/plain');
			$this->Response->setContent(xdiff_string_diff(
				$versions[$values['version_a']],
				$versions[$values['version_b']]
			));
		}
		
		public function create() {
			$this->form();
			
			if ($this->form->wasSubmitted() and EncodingProfile::create($this->form->getValues())) {
				$this->flash('Encoding profile created');
				return $this->redirect('encodingprofiles', 'index');
			}
			
			$this->profiles = EncodingProfile::findAll()
				->select('id, name')
				->orderBy('slug')
				->indexBy('id', 'name');
			
			return $this->render('encoding/profiles/edit');
		}
		
		public function edit(array $arguments) {
			$this->profile = EncodingProfile::findOrThrow($arguments['id']);
			
			$this->form();
			
			if ($this->form->getValue('save') and $this->profile->save($this->form->getValues())) {
				$error = EncodingProfileVersion::isTemplateValid($this->form->getValue('xml_template'));
				
				// TODO: move to Model validation
				if ($error !== true) {
					$this->flashNow('Template error: ' . $error);
				} else {
					if ($this->form->getValue('create_version')) {
						$version = new EncodingProfileVersion([
							'encoding_profile_id' => $this->profile['id']
							// TODO: save based version
						]);
						
						$this->flash('Encoding profile updated, new profile version created');
					} else {
						$version = EncodingProfileVersion::find($this->form->getValue('version'));
						$this->flash('Encoding profile updated');
					}
					
					$version->save($this->form->getValues());
					
					return $this->redirect('encodingprofiles', 'index');
				}
			}
			
			if ($this->form->wasSubmitted()) {
				$this->version = EncodingProfileVersion::findBy([
					'id' => $this->form->getValue('version'),
					'encoding_profile_id' => $arguments['id']
				], [], []);
			} elseif (isset($arguments['version'])) {
				$this->version = EncodingProfileVersion::findBy([
					'id' => $arguments['version'],
					'encoding_profile_id' => $arguments['id']
				], [], []);
			} else {
				$this->version = $this->profile->LatestVersion;
			}
			
			$this->versions = $this->profile
				->Versions
				->orderBy('revision DESC')
				->select('id, revision, description, created');
			$this->profiles = EncodingProfile::findAll()
				->select('id, name')
				->orderBy('slug')
				->whereNot(['id' => $this->profile['id']])
				->indexBy('id', 'name');
			
			return $this->render('encoding/profiles/edit');
		}
		
		public function delete(array $arguments) {
			$profile = EncodingProfile::findOrThrow($arguments['id']);
			
			if ($profile->destroy()) {
				$this->flash('Encoding profile ' . $profile['name'] . ' deleted');
			}
			
			return $this->View->redirect('encodingprofiles', 'index');
		}
		
	}
	
?>