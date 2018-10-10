<?php
/**
 * Litus is a project by a group of students from the KU Leuven. The goal is to create
 * various applications to support the IT needs of student unions.
 *
 * @author Niels Avonds <niels.avonds@litus.cc>
 * @author Karsten Daemen <karsten.daemen@litus.cc>
 * @author Koen Certyn <koen.certyn@litus.cc>
 * @author Bram Gotink <bram.gotink@litus.cc>
 * @author Dario Incalza <dario.incalza@litus.cc>
 * @author Pieter Maene <pieter.maene@litus.cc>
 * @author Kristof Mariën <kristof.marien@litus.cc>
 * @author Lars Vierbergen <lars.vierbergen@litus.cc>
 * @author Daan Wendelen <daan.wendelen@litus.cc>
 * @author Mathijs Cuppens <mathijs.cuppens@litus.cc>
 * @author Floris Kint <floris.kint@vtk.be>
 *
 * @license http://litus.cc/LICENSE
 */

namespace CommonBundle\Component\ServiceManager\ServiceLocatorAware;

/**
 * A trait to define some common methods for classes with a ServiceLocator.
 * This trait requires the class to implement
 * CommonBundle\Component\ServiceManager\ServiceLocatorAwareInterface.
 *
 * @see CommonBundle\Component\ServiceManager\ServiceLocatorAwareInterface
 * @author Pieter Maene <pieter.maene@litus.cc>
 */

trait SentryTrait
{
    /**
     * @return bool
     */
    protected function hasSentry()
    {
        return $this->getServiceLocator()->has('sentry');
    }

    /**
     * @return Client
     */
    protected function getSentry()
    {
        return $this->getServiceLocator()->get('sentry');
    }

    /**
     * @return ServiceLocatorInterface
     */
    abstract public function getServiceLocator();
}
