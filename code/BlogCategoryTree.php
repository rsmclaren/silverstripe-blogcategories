<?php
/**
 * An extension to the @see BlogHolder class
 * @author Ryan McLaren
 */
class BlogCategoryTree extends DataExtension{
    
    public static $has_many=array(
        'BlogCategories'=>'BlogCategory', // only relates to BlogHolder, not BlogTree
    );
    
    /**
     * updates the fields used in the CMS
     * @see DataExtension::updateCMSFields()
     * @TODO remove the add/edit buttons from the authors gridfield
     */
    public function updateCMSFields(FieldList $fields){
        // Categories tab: Show either on BlogTree or BlogHoder depending on limit settings
        $limit = Config::inst()->get('BlogCategory', 'limit_to_holder');
        if(
            ($limit && $this->owner instanceof BlogHolder)
            || (!$limit && !($this->owner instanceof BlogHolder)) // applies to BlogTree
        ) {
            $categories = ($limit) ? $this->owner->BlogCategories() : BlogCategory::get();
            $fields->addFieldToTab(
                'Root.Categories', 
                GridField::create('BlogCategories', 'Blog Categories', $categories, GridFieldConfig_RecordEditor::create())
            );    
        }        
    }	
    
}

class BlogCategoryTreeExtension_Controller extends DataExtension {
    
    public static $allowed_actions=array(
        'category',
        'categoryindex',
    );
    
    /**
     * shows only blog entires
     * associated with the currently selected
     * category. the current blog category is determined
     * through the URLParam ID
     * @param SS_HTTPRequest $request
     * @return {mixed} template to renderWith or httpError
     */
    public function category(SS_HTTPRequest $request){
        $params = $this->owner->getURLParams();
        
        //get the urlSegment safe for the DB
        $urlSegment = Convert::raw2sql($params['ID']);
        
        if( ($urlSegment != NULL) && (DataList::create('BlogCategory')->where("URLSegment = '$urlSegment'")->count() >= 1 )){
            
            //the category exists - get the id
            $category = DataList::create('BlogCategory')->where("URLSegment = '$urlSegment'")->first();

            $categoryID = $category->getField('ID');
            
            //sort order
            $order = '"BlogEntry"."Date" DESC';                      
            
            //get the blog entries
            $entries = BlogEntry::get()
            ->where('"BlogEntry" . "ID" = "BlogEntry_BlogCategories" . "BlogEntryID"')
            ->innerJoin('BlogEntry_BlogCategories', 'BlogEntry_BlogCategories.BlogCategoryID ='. $categoryID)
            ->sort($order);             
             
            //wrap in a paginated list
            $list = new PaginatedList($entries, Controller::curr()->request);
                                     
            $data =array(
                        'BlogEntries'=> $list,
                        'BlogCategory' => $category->getField('Title')
                    );
            
            return $this->owner->customise($data)->renderWith(array('BlogHolder', 'Page'));
            
        } else {
            
            //no category selected
			return $this->owner->httpError(404, "You must select a category or that category doesn't exist");	
			
        }
    }

    public function categoryindex(SS_HTTPRequest $request) {
        $limit = Config::inst()->get('BlogCategory', 'limit_all_tags');
        return $this->owner->customise(array(
            'BlogCategoryCloud' => $this->getBlogCategoryCloud($limit)
        ))->renderWith(array('BlogHolder_categoryindex', 'Page'));
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