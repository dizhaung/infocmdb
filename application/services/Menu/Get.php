<?php

/**
 *
 *
 *
 */
class Service_Menu_Get extends Service_Abstract
{

    public function __construct($translator, $logger, $themeId)
    {
        parent::__construct($translator, $logger, 3801, $themeId);
    }

    /**
     * retrieves a list of attributes by the given restrictions
     *
     * @param int    $page      the page to display
     * @param string $orderBy   name to sort by
     * @param string $direction ASC/DESC
     * @param string $filter
     */
    public function getMenuList($page = null, $orderBy = null, $direction = null, $filter = null)
    {
        $this->logger->log("Service_Menu: getMenuList('$page', '$orderBy', '$direction', '$filter') has been invoked", Zend_Log::DEBUG);

        $config            = new Zend_Config_Ini(APPLICATION_PATH . '/configs/pagination/menu.ini', APPLICATION_ENV);
        $itemsCountPerPage = $config->pagination->itemsCountPerPage;
        $itemsPerPage      = $config->pagination->itemsPerPage;
        $scrollingStyle    = $config->pagination->scrollingStyle;
        $scrollingControl  = $config->pagination->scrollingControl;

        if (is_null($page)) {
            $this->logger->log('Service_Menu: getMenuList page var was null. using default value 1 for user display', Zend_Log::DEBUG);
            $page = 1;
        }

        $menuDaoImpl = new Dao_Menu();

        $select = $menuDaoImpl->getMenuPagination($orderBy, $direction, $filter);

        unset($menuDaoImpl);


        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($itemsCountPerPage);

        Zend_Paginator::setDefaultScrollingStyle($scrollingStyle);
        Zend_View_Helper_PaginationControl::setDefaultViewPartial(
            $scrollingControl
        );

        return $paginator;
    }

    /**
     * @param string $filter
     */
    public function getFilterForm($filter = null)
    {
        $form = new Form_Filter($this->translator);

        if ($filter) {
            $form->populate(array('search' => $filter));
        }
        return $form;
    }

}
