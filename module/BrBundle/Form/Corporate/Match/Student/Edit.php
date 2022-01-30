<?php

namespace BrBundle\Form\Corporate\Match\Student;

use BrBundle\Entity\Match\Profile;

/**
 * Add Profile
 *
 * @author Robin Wroblowski <robin.wroblowski@vtk.be>
 */
class Edit extends Add
{
    /**
     * @var Profile the profile
     */
    private $profile;

    public function init()
    {

        parent::init();

        $this->remove('submit')
            ->addSubmit('Save Changes');
        $this->remove('conditions');

        if ($this->profile !== null) {
            $hydrator = $this->getHydrator();
            $this->populateValues($hydrator->extract($this->profile));
        }
    }

    public function setProfile(Profile $profile)
    {
        $this->profile = $profile;

        return $this;
    }
}
