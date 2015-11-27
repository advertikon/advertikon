<?php
/**
* Form Collection element for custom fields indexes
*
* @author Advertikon
* @copyright
* @package Advertikon
* @subpackage Board
*/

namespace Advertikon\Form\Element;

use Zend\Form\FormInterface;
use Advertikon\Exception\InvalidArgument;
use Zend\Form\FieldsetInterface;
use Advertikon\Form\DoPrepareAwareInterface;
use Advertikon\Form;

/**
* Custom collection form class
*/
class CustomCollection extends Collection implements DoPrepareAwareInterface {

	/**
	* @var Array $_collaction Collection of fields names
	*/
	protected $_collection = [];

    /**
     * @see Zend\Form\Element\Collection
     */
    public function populateValues($data) {
        if (!is_array($data) && !$data instanceof Traversable) {
            throw new InvalidArgument(sprintf(
                '%s expects an array or Traversable set of data; received "%s"',
                __METHOD__,
                (is_object($data) ? get_class($data) : gettype($data)) 
            ));
        }

        // Can't do anything with empty data
        if (empty($data)) {
            return;
        }

        if (!$this->allowRemove && count($data) < $this->count) {
            throw new InvalidArgument(sprintf(
                'There are fewer elements than specified in the collection (%s). Either set the allow_remove option '
                . 'to true, or re-submit the form.',
                get_class($this)
            ));
        }

        // Check to see if elements have been replaced or removed
        $toRemove = [];
        foreach ( $this as $name => $elementOrFieldset ) {
            if ( isset( $data[ $name ] ) || $elementOrFieldset->getOption( 'doNotRemoveOnPopulateValues' ) ) {
                continue;
            }

            if (!$this->allowRemove) {
                throw new InvalidArgument(sprintf(
                    'Elements have been removed from the collection (%s) but the allow_remove option is not true.',
                    get_class($this)
                ));
            }

            $toRemove[] = $name;
        }

        foreach ($toRemove as $name) {
			$this->remove($name);
        }

        foreach ($data as $key => $value) {
            if ($this->has($key)) {
                $elementOrFieldset = $this->get($key);
            } else {
                $elementOrFieldset = $this->addNewTargetElementInstance( $key );
                $count = $this->getIterator()->count() - 1;
                if ($count > $this->lastChildIndex) {
                    $this->lastChildIndex = $count;
                }
            }

            if ($elementOrFieldset instanceof FieldsetInterface) {
                $elementOrFieldset->populateValues($value);
            } else {
                $elementOrFieldset->setAttribute('value', $value);
            }
        }

        if (!$this->createNewObjects()) {
            $this->replaceTemplateObjects();
        }

       $this->doPrepare(); 
    }

    /**
     * @see Zend\Form\Element\Collection
     */
    public function prepareElement( FormInterface $form ) { 

    	Form::workOnElement( $form->getInputByElement( $this ) , $this->getTargetElement() );

        if ( true === $this->shouldCreateChildrenOnPrepareElement ) {
            if ( $this->targetElement !== null && $this->count > 0 && $this->_collection ) {
                while ( $this->count > $this->lastChildIndex + 1 && $this->lastChildIndex + 2 <= count( $this->_collection ) ) {
                    $this->addNewTargetElementInstance( $this->_collection[ ++$this->lastChildIndex ] );
                }
            }
        }

        // Create a template that will also be prepared
        if ($this->shouldCreateTemplate) {
            $templateElement = $this->getTemplateElement();
            $this->add($templateElement);

            parent::prepareElement($form);

        // The template element has been prepared, but we don't want it to be
        // rendered nor validated, so remove it from the list.
        if ($this->shouldCreateTemplate) {
            $this->remove($this->templatePlaceholder);
        }
    }
}

      /**
	* @see Advertikon\Form\DoPrepareAwareInterface::doPrepare()
	*/
	public function doPrepare() {
		foreach( $this->getIterator() as $name => $element ) {
			if( ! preg_match( '/[\]\[]/', $name ) ) {
				$element->setName( $this->getName() . '[' . $name . ']' );
			}
			if( $element instanceof DoPrepareAwareInterface ) {
				$element->doPrepare();
			} 
		}
    }

}