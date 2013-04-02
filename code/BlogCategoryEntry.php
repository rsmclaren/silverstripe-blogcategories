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

        // Try to fetch categories from cache                
        $categories = $this->getAllBlogCategories();
        if($categories->count() >= 1){
            $categoryList = "<ul>";
            foreach ($categories->column('Title') as $title) {
                $categoryList .= "<li>" .Convert::raw2xml($title). "</li>";
            }
            $categoryList .="</ul>";
        }else {
            $categoryList="<ul><li>No categories have been added. Add categories from the parent blog holder.</li></ul>";            
        }

        //categories tab
        $gridFieldConfig = GridFieldConfig_RelationEditor::create();
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

        // Optionally default category to current holder
        if(Config::inst()->get('BlogCategory', 'limit_to_holder')) {
            $holder = $this->owner->Parent();
            $gridFieldConfig->getComponentByType('GridFieldDetailForm')
                ->setItemEditFormCallback(function($form, $component) use($holder) {
                    $form->Fields()->push(HiddenField::create('ParentID', false, $holder->ID));
                });
        }
    } 

   /**
    * returns a DataObjectSet of all the BlogCategories
    * @return {DataObjectSet}
    */
   public function getAllBlogCategories(){
        if(Config::inst()->get('BlogCategory', 'limit_to_holder')) {
            return $this->owner->Parent()->BlogCategories();     
        } else {
            return BlogCategory::get(); 
        }
       
   }

   /**
    * @param Int $limit
    * @return BlogCategoryCloud
    */
   public function getBlogCategoryCloud($limit = 10) {
     $cloud = BlogCategoryCloud::create();
     if(Config::inst()->get('BlogCategory', 'limit_to_holder')) {
        $cloud->setHolderId($this->owner->ParentID);
     }
     if($limit) $cloud->setLimit($limit);
     
     return $cloud;
   }

   public function getBlogCategoriesMoreLink() {
    if(Config::inst()->get('BlogCategory', 'limit_to_holder')) {
        $parent = $this->owner->Parent();
    } else {
        $parent = BlogTree::get()->filter('ClassName', 'BlogTree')->First();
        if(!$parent) $parent = BlogHolder::get()->First();
    }
    return $parent->Link('categoryindex');
   }
    
}

?>