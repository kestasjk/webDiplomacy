UPDATE `wD_Misc` SET `value` = '184' WHERE `name` = 'Version';

-- A third map UI: the classic board.php page with the canvas point-and-click map (javascript/wd/canvasboard.js)
-- in place of the map image and the drop-down order form. See Game::useCanvasBoard().
ALTER TABLE `wD_UserOptions` CHANGE COLUMN `mapUI` `mapUI` enum('Point and click','Dropdown menus','Legacy point and click') NOT NULL DEFAULT 'Point and click';
