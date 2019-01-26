<?php
namespace T3\Dce\Hooks;

/*  | This extension is made for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2012-2019 Armin Vieweg <armin@v.ieweg.de>
 */
use T3\Dce\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PageLayoutView DrawItem Hook for DCE content elements
 */
class PageLayoutViewDrawItemHook implements \TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface
{
    /**
     * @var bool
     */
    protected $stylesAdded = false;

    /**
     * Disable rendering restrictions for dce content elements
     *
     * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject
     * @param $drawItem
     * @param $headerContent
     * @param $itemContent
     * @param array $row #
     * @return void
     * @throws \TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException
     */
    public function preProcess(
        \TYPO3\CMS\Backend\View\PageLayoutView &$parentObject,
        &$drawItem,
        &$headerContent,
        &$itemContent,
        array &$row
    ) {
        $dceUid = DatabaseUtility::getDceUidByContentElementRow($row);
        if ($dceUid === 0) {
            return;
        }

        try {
            /** @var \T3\Dce\Domain\Model\Dce $dce */
            $dce = \T3\Dce\Utility\DatabaseUtility::getDceObjectForContentElement($row['uid']);
        } catch (\Exception $exception) {
            $headerContent = '<strong class="text-danger">' . $exception->getMessage() .'</strong>';
            return;
        }

        $drawItem = false;
        if ($dce->isUseSimpleBackendView()) {
            $this->addPageViewStylesheets();

            /** @var \T3\Dce\Components\BackendView\SimpleBackendView $simpleBackendView */
            $simpleBackendView = GeneralUtility::makeInstance(
                \T3\Dce\Components\BackendView\SimpleBackendView::class
            );

            $headerContent = $parentObject->linkEditContent(
                $simpleBackendView->getHeaderContent($dce),
                $row
            );
            $itemContent .= $parentObject->linkEditContent(
                $simpleBackendView->getBodytextContent($dce, $row),
                $row
            );
        } else {
            $headerContent = $parentObject->linkEditContent($dce->renderBackendTemplate('header'), $row);
            $itemContent .= $parentObject->linkEditContent($dce->renderBackendTemplate('bodytext'), $row);
        }
    }

    /**
     * Add custom dce styles for Simple Backend View to page module
     *
     * @return void
     */
    protected function addPageViewStylesheets()
    {
        if ($this->stylesAdded) {
            return;
        }
        /** @var \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
        $pageRenderer->addCssInlineBlock(
            'DcePageLayoutStyles',
            file_get_contents(ExtensionManagementUtility::extPath('dce') . 'Resources/Public/Css/dceInstance.css')
        );
        $this->stylesAdded = true;
    }
}
