<?php
/**
 * an extension to the @see BlogEntry class
 * @author Ryan McLaren
 */
class BlogCategoryEntry extends DataExtension {
    
    public static $many_many=array(
                                    'BlogCategories'=>'BlogCategory'
                                );
    
    /**
     * Updates the fields used in the CMS
     * @see DataExtension::updateCMSFields()     
     */
    public function updateCMSFields(FieldList $fields){
        
        Requirements::CSS('BlogCategories/css/cms-blog-categories.css');
        
        //main tab
        $fields->addFieldToTab('Root.Main', new UploadField('AbstractImage', 'Abstract Image - 160x155px'));
        
        //creating a list of categories for the togglecompositefield
        if($this->owner->Parent->BlogCategories()->count() >= 1){
            $categoryList = "<ul>";
            foreach ($this->owner->Parent->BlogCategories() as $category){
                $categoryList .= "<li>" .$category->Title. "</li>";
            }
            $categoryList .="</ul>";
        }else {
            $categoryList="<ul><li>No categories have been added. Add categories from the parent blog holder.</li></ul>";            
        }

        //categories tab
        $gridFieldConfig = GridFieldConfig_RelationEditor::create();
        $gridFieldConfig->removeComponentsByType('GridFieldAddNewButton');
        $fields->addFieldToTab('Root.Categories', GridField::create('BlogCategories', 'Blog Categories', $this->owner->BlogCategories(), $gridFieldConfig));
        $fields->addFieldToTab('Root.Categories',
                ToggleCompositeField::create(
                        'ExistingCategories',
                        'View Existing Categories',
                        array(
                                new LiteralField("CategoryList", $categoryList)
                        )
                )->setHeadingLevel(4)
        );
    } 
   
   /**
    * returns a DataObjectSet of all the BlogCategories
    * @return {DataObjectSet}
    */
   public function getAllBlogCategories(){
       return $this->owner->Parent()->BlogCategories();
   }

   /**
    * @return BlogCategoryCloud
    */
   public function getBlogCategoryCloud() {
     return BlogCategoryCloud::create()
        ->setHolderId($this->owner->ParentID)
        ->setLimit(10);
   }
    
}

?>