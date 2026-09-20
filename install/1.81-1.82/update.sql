UPDATE `wD_Misc` SET `value` = '182' WHERE `name` = 'Version';

-- Fix the wD.Test.3 DATC test data, which has never been able to run.
--
-- The test (added with the self-dislodgement/paradox adjudicator fix in 1.71-1.72) never got as far
-- as adjudicating, because the DATC harness enters its orders through the real order interface and
-- two of its rows describe orders that interface will not accept:
--
-- 1. The Turkish fleet in Rumania was ordered to Bulgaria (20). Bulgaria is a coast parent, so a
--    fleet has to be given a coast; from Rumania only Bulgaria (North Coast), 80, is reachable.
--    wD.Test.2 already gets this right for its own fleet move into Bulgaria.
-- 2. The Italian fleet supporting Constantinople -> Black Sea was placed in Ankara (24), which the
--    Turkish army in the same test already occupies. datcGame::loadUnits() inserts one unit per row,
--    so that put two units in Ankara, and every join in the harness matches units on terrID alone,
--    which then applies both orders to both units. Armenia (25) is the free territory next to the
--    Black Sea, so the support is moved there.
--
-- An order that the interface rejects is never submitted (form.js only posts complete orders), so it
-- stays as processOrderDiplomacy::create() inserted it, a Hold, and the test reports the given order
-- not matching the received one.
UPDATE wD_DATCOrders SET toTerrID = 80 WHERE testID = 903 AND terrID = 21 AND countryID = 7 AND moveType = 'Move';
UPDATE wD_DATCOrders SET terrID = 25 WHERE testID = 903 AND terrID = 24 AND countryID = 3 AND moveType = 'Support move';
