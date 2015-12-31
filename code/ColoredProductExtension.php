<?php

class ColoredProductExtension extends DataExtension
{

    private static $many_many = array(
        "Images" => "Image"
    );

    private static $many_many_extraFields = array(
        'Images' => array(
            'ColorID' => "Int",
            'Sort' => "Int"
        )
    );

    public function updateCMSFields(FieldList $fields)
    {
        $fields->insertAfter($tabset = new TabSet('ColoredImages'), 'Image');
        $tabset->push($uploadtab = new Tab('UploadImages'));
        $tabset->push($attributetab = new Tab('AssignAttribute'));

        $uploadtab->push($uf = new UploadField('Images', 'Images'));
        $uf->setDescription('Note: The product must be saved before attributes can be assigned to new uploaded images.');

        $attributetab->push(
            $gf = GridField::create("ImageAttributes", "Images", $this->owner->Images(),
                GridFieldConfig_RelationEditor::create()
                    ->removeComponentsByType("GridFieldAddNewButton")
                    ->removeComponentsByType("GridFieldEditButton")
                    ->removeComponentsByType("GridFieldDataColumns")
                    ->removeComponentsByType("GridFieldDeleteAction")
                    ->addComponent(
                        $cols = new GridFieldEditableColumns()
                    )
                    ->addComponent(
                        new GridFieldOrderableRows('Sort')
                    )
            )
        );
        $displayfields = array(
            'Title' => array(
                'title' => 'Title',
                'field' => new ReadonlyField("Name")
            )
        );
        //add drop-down color selection
        $colors = $this->owner->getColors();
        if ($colors->exists()) {
            $displayfields['ColorID'] = function ($record, $col, $grid) use ($colors) {
                return DropdownField::create($col, "Color",
                    $colors->map('ID', 'Value')->toArray()
                )->setHasEmptyDefault(true);
            };
        }
        $cols->setDisplayFields($displayfields);
    }

    /**
     * Get all the colors for this product
     * @return DataList colored attribute values
     */
    public function getColors()
    {
        return ColoredProductAttributeValue::get()
            ->innerJoin(
                "ProductVariation_AttributeValues",
                "\"ProductVariation_AttributeValues\".\"ProductAttributeValueID\" = ".
                    "\"ColoredProductAttributeValue\".\"ID\""
            )
            ->innerJoin(
                "ProductVariation",
                "\"ProductVariation_AttributeValues\".\"ProductVariationID\" = ".
                    "\"ProductVariation\".\"ID\""
            )
            ->filter("ProductID", $this->owner->ID);
    }

    /**
     * Add image lists to colors;
     * @return DataList colors list customised with image lists
     */
    public function Colors()
    {
        $colors = $this->getColors();
        $images = $this->owner->Images();
        
        if (!$images->exists()) {
            return $colors;
        }

        //add images to output
        $output = new ArrayList();

        foreach ($colors as $color) {
            $output->push($color->customise(array(
                'Images' => $images->filter('ColorID', $color->ID)
            )));
        }

        return $output;
    }
}
