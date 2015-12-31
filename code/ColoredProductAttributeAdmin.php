<?php

class ColoredProductAttributeAdmin extends Extension
{

    public function updateEditForm($form)
    {
        $this->updateCMSFields($form->Fields());
    }
    
    public function updateCMSFields(FieldList $fields)
    {
        if ($attributes = $fields->fieldByName("ProductAttributeType")) {
            $attributes->getConfig()
                ->removeComponentsByType("GridFieldAddNewButton")
                ->addComponent(
                    $multiclass = new GridFieldAddNewMultiClass()
                );
            $multiclass->setClasses(
                array_values(ClassInfo::subclassesFor("ProductAttributeType"))
            );
        }
    }
}
