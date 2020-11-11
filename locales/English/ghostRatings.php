<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * Ghost Ratings Explained - full render
 * 
 * @package Base
 * @subpackage Static
 */

print libHTML::pageTitle('Ghost Ratings Explained','A comprehensive guide to webDiplomacy\'s Ghost Ratings'); 
?>

<div class="gr-guide-container">
    <div style="text-align: center;">
        <a href="#gr_generic">The Basics</a> - <a href="#gr_advanced">Advanced Calculations</a> - <a href="#gr_categories">By Category</a>
    </div>

    <div class="hr"></div>

    <div class="gr-guide-section">
        <a name="gr_generic"></a>
        <div class="gr-guide-section-title">
            <h2>The Basics</h2>
        </div>
        <div class="gr-guide-segment-wrapper">
            <button class="gr-guide-switch gr-guide-switch-default-open">
                What are the Ghost Ratings?
            </button>
            <div class="gr-guide-detail">
                <p>
                    The Ghost Ratings are a group of true skill ratings developed on webDiplomacy by TheGhostmaker. 
                    The purpose of the Ghost Ratings is to approximate the skill level of webDiplomacy players relative 
                    to the rest of the players on the site. Since their development well over a decade ago, the Ghost 
                    Ratings have been tweaked and fully integrated into webDiplomacy. This page explains their implementation 
                    on webDiplomacy. 
                </p>
            </div>

            <button class="gr-guide-switch">
                What are the Ghost Rating categories?
            </button>
            <div class="gr-guide-detail">
                <p>
                    The Ghost Ratings are broken into seven categories: overall, gunboat, full press, live, 1v1 overall ELO, 
                    FvA ELO, and GvI ELO. The way of these categories is handled is described in detail 
                    <a href="#gr_categories" class="light">below</a>.
                </p>
            </div>

            <button class="gr-guide-switch">
                Where do I see my ratings?
            </button>
            <div class="gr-guide-detail">
                <p>
                    You can visit your <a href="userprofile.php?userID=<?php print($User->id)?>" class="light">profile</a> 
                    to see how you stack up. Just expand the Ghost Ratings categories in the middle section to see your 
                    current rating and rank among all players. You can also see how you trend over time. 
                    <br><br>
                    To see a more sophisticated breakdown of your GR, visit our 
                    <a href="grCategoryStats.php" class="light">detailed GR breakdown page</a>.
                </p>
            </div>
        </div>
    </div>

    <div class="gr-guide-section">
        <a name="gr_advanced"></a>
        <div class="gr-guide-section-title">
            <h2>Advanced Calculations</h2>
        </div>
        <div class="gr-guide-segment-wrapper">
            <button class="gr-guide-switch gr-guide-switch-default-open">
                How are the Ghost Ratings calculated?
            </button>
            <div class="gr-guide-detail">
                <p>
                    There are two formulas used to calculate your GR, one for 1v1 games and one for non-1v1 games. Both of 
                    these formulas are based on the ELO rating system. However, they have different ways of computing the 
                    score that you are expected to attain given the ratings of the competition you are playing against and 
                    the actual score that you achieve in a given game.
                    <br><br>
                    In both 1v1 and non-1v1 games, your rating is calculated using the following general formula:
                </p>
    
                <div class="gr-guide-code">
                    ratingAdjustment = x * (expectedScore - actualScore)
                </div>
                    
                <h3 style="margin-left: 0;">Classic Formula</h3>
                <p>
                    In the non-1v1 formula, "x" represents a scaling modifier that changes based on the settings of the game, 
                    derived by the following equation:
                </p>
    
                <div class="gr-guide-code">
                    x = sumOfEachPlayersGR / (17.5 * pressWeightModifier * variantWeightModifier)
                </div>
                
                <p>
                    The press weight modifiers and variant weight modifiers for each category is listed with the category details.
                    <br><br>
                    We calculate your expected score in a non-1v1 game with the following equation:
                </p>
    
                <div class="gr-guide-code">
                    expectedScore = yourGR / sumOfEachPlayersGR
                </div>
                 
                <p>
                    If you draw in a draw-size scoring (DSS/WTA) game, we calculate your actual score with the following equation:
                </p>
                    
                <div class="gr-guide-code">
                    actualScore = 1 / numberOfPlayersInTheDraw
                </div>
                
                <p>
                    If you draw in a sum-of-squares scoring (SoS) game, we calculate your actual score with the following equation:
                </p>
    
                <div class="gr-guide-code">
                    actualScore = yourSupplyCenters ^ 2 / allSupplyCenters ^ 2
                </div>
                
                <p>
                    Your actual score in a non-1v1 game is always 1 if you solo and 0 if you are defeated.
                </p>

                <h3 style="margin-left: 0;">1v1 Formula</h3>
                <p>
                    In a 1v1 formula, "x" is always 32. This value is constant, and therefore never changes. 
                    <br><br>
                    We calculate your expected score in a 1v1 game with the following equation:
                </p>

                <div class="gr-guide-code">
                    expectedScore = (10 ^ (yourGR / 400)) / ((10 ^ (yourGR / 400)) + (10 ^ (opponentGR / 400)))
                </div>

                <p>
                    Your actual score in a 1v1 game is 1 if you solo, 0.5 if you draw, and 0 if you are defeated.
                </p>
                </div>
            </div>
        </div>
    </div>

    <div class="gr-guide-section">
        <a name="gr_categories"></a>
        <div class="gr-guide-section-title">
            <h2>Ghost Ratings Specifics By Category</h2>
        </div>

        <div class="gr-guide-segment-wrapper">
            <button class="gr-guide-switch gr-guide-switch-default-open">
                Category Modifiers
            </button>
            <div class="gr-guide-detail">
                <p>
                    In each category, each variants, press type, phase length, and scoring system that counts toward that category is 
                    displayed. Additionally, the press or variant weight modifiers applicable to each category are listed with the 
                    eligible variants and press types. These modifiers determine how large or small of an adjustment you will receive 
                    to your rating when a game concludes. A small modifier means that you will receive a <i>larger</i> adjustment. To show 
                    an example of this, let's use the modifier piece of non-1v1 formula, where "x" is a constant used to determine how large 
                    or small of an adjustment you receive to the difference between your expected score and actual score:
                </p>

                <div class="gr-guide-code">
                    x = sumOfEachPlayersGR / (17.5 * pressWeightModifier * variantWeightModifier)
                </div>

                <p>
                    A classic map, full press game in the Overall category has a variant modifier of 1 and a press type modifier of 1. 
                    Thus, the value for "x" is the sum of each player's GR divided by 17.5 (17.5 * 1 * 1). In an Ancient Meditteranean map, 
                    gunboat game in the Overall category, the variant modifier is 2 and the press type modifier is 4. Therefore, the value 
                    for "x" is the sum of each player's GR divided by 140 (17.5 * 2 * 4). If the sum of each player's GR in both of these 
                    games is 200, then "x" would be 11.43 in the classic, full press game, and 1.43 in the Ancient Meditteranean gunboat game. 
                    Plugging each of these values into the following formula, you can see that you will receive an adjustment 8 times larger 
                    for a classic full press game than an Ancient Mediterranean gunboat game.
                </p>

                <div class="gr-guide-code">
                    ratingAdjustment = x * (expectedScore - actualScore)
                </div>

                <p>
                    Keep in mind that we treat 1v1 differently. The value of "x" above will always be 32 in 1v1, and thus there are no 
                    press type or variant modifiers in 1v1 games. With this in mind, click a category below to view which variants, press 
                    types, scoring systems, and phase lengths are factored into each category ranking, and the modifiers we use to derive 
                    your score.
                </p>
            </div>

            <button class="gr-guide-switch">
                Overall
            </button>
            <div class="gr-guide-detail">
                <p>
                    <h4>Variants</h4>
                    <ul>
                        <li>Classic (modifier: 1)</li>
                        <li>Ancient Mediterranean (modifier: 2)</li>
                        <li>World Diplomacy IX (modifier: 4)</li>
                        <li>Fall of the American Empire (modifier: 4)</li>
                        <li>Modern Diplomacy II (modifier: 4)</li>
                        <li>Classic - Chaos (modifier: 8)</li>
                    </ul>
    
                    <h4>Press Types</h4>
                    <ul>
                        <li>Full Press (modifier: 1)</li>
                        <li>Rulebook Press (modifier: 1)</li>
                        <li>Public Press (modifier: 2)</li>
                        <li>Gunboat (modifier: 4)</li>
                    </ul>
    
                    <h4>Scoring Systems</h4>
                    <ul>
                        <li>Draw-size scoring</li>
                        <li>Sum-of-squares scoring</li>
                    </ul>
    
                    <h4>Phase Lengths</h4>
                    <ul>
                        <li>1+ hour/phase</li>
                        <li>5 minute/phase - 30 minute/phase</li>
                    </ul>
                </p>
            </div>
        </div>

        <div class="gr-guide-segment-wrapper">
            <button class="gr-guide-switch">
                Gunboat
            </button>
            <div class="gr-guide-detail">
                <p>
                    <h4>Variants</h4>
                    <ul>
                        <li>Classic (modifier: 1)</li>
                        <li>Ancient Mediterranean (modifier: 2)</li>
                        <li>World Diplomacy IX (modifier: 4)</li>
                        <li>Fall of the American Empire (modifier: 4)</li>
                        <li>Modern Diplomacy II (modifier: 4)</li>
                        <li>Classic - Chaos (modifier: 8)</li>
                    </ul>
    
                    <h4>Press Types</h4>
                    <ul>
                        <li>Gunboat (modifier: 1)</li>
                    </ul>
    
                    <h4>Scoring Systems</h4>
                    <ul>
                        <li>Draw-size scoring</li>
                        <li>Sum-of-squares scoring</li>
                    </ul>
    
                    <h4>Phase Lengths</h4>
                    <ul>
                        <li>1+ hour/phase</li>
                        <li>5 minute/phase - 30 minute/phase</li>
                    </ul>
                </p>
            </div>
        </div>

        <div class="gr-guide-segment-wrapper">
            <button class="gr-guide-switch">
                Live
            </button>
            <div class="gr-guide-detail">
                <p>
                    <h4>Variants</h4>
                    <ul>
                        <li>Classic (modifier: 1)</li>
                        <li>Ancient Mediterranean (modifier: 2)</li>
                        <li>World Diplomacy IX (modifier: 4)</li>
                        <li>Fall of the American Empire (modifier: 4)</li>
                        <li>Modern Diplomacy II (modifier: 4)</li>
                        <li>Classic - Chaos (modifier: 8)</li>
                    </ul>
    
                    <h4>Press Types</h4>
                    <ul>
                        <li>Full Press (modifier: 1)</li>
                        <li>Rulebook Press (modifier: 1)</li>
                        <li>Public Press (modifier: 2)</li>
                        <li>Gunboat (modifier: 4)</li>
                    </ul>
    
                    <h4>Scoring Systems</h4>
                    <ul>
                        <li>Draw-size scoring</li>
                        <li>Sum-of-squares scoring</li>
                    </ul>
    
                    <h4>Phase Lengths</h4>
                    <ul>
                        <li>5 minute/phase - 30 minute/phase</li>
                    </ul>
                </p>
            </div>
        </div>

        <div class="gr-guide-segment-wrapper">
            <button class="gr-guide-switch">
                Full Press
            </button>
            <div class="gr-guide-detail">
                <p>
                    <h4>Variants</h4>
                    <ul>
                        <li>Classic (modifier: 1)</li>
                    </ul>
    
                    <h4>Press Types</h4>
                    <ul>
                        <li>Full Press (modifier: 1)</li>
                        <li>Rulebook Press (modifier: 1)</li>
                    </ul>
    
                    <h4>Scoring Systems</h4>
                    <ul>
                        <li>Draw-size scoring</li>
                        <li>Sum-of-squares scoring</li>
                    </ul>
    
                    <h4>Phase Lengths</h4>
                    <ul>
                        <li>1+ hour/phase</li>
                    </ul>
                </p>
            </div>
        </div>

        <div class="gr-guide-segment-wrapper">
            <button class="gr-guide-switch">
                1v1 Overall
            </button>
            <div class="gr-guide-detail">
                <p>
                    <h4>Variants</h4>
                    <ul>
                        <li>France vs Austria</li>
                        <li>Germany vs Italy</li>
                    </ul>
    
                    <h4>Phase Lengths</h4>
                    <ul>
                        <li>1+ hour/phase</li>
                        <li>5 minute/phase - 30 minute/phase</li>
                    </ul>
                </p>
            </div>
        </div>

        <div class="gr-guide-segment-wrapper">
            <button class="gr-guide-switch">
                1v1 FvA
            </button>
            <div class="gr-guide-detail">
                <p>
                    <h4>Variants</h4>
                    <ul>
                        <li>France vs Austria</li>
                    </ul>
    
                    <h4>Phase Lengths</h4>
                    <ul>
                        <li>1+ hour/phase</li>
                        <li>5 minute/phase - 30 minute/phase</li>
                    </ul>
                </p>
            </div>
        </div>

        <div class="gr-guide-segment-wrapper">
            <button class="gr-guide-switch">
                1v1 GvI
            </button>
            <div class="gr-guide-detail">
                <p>
                    <h4>Variants</h4>
                    <ul>
                        <li>Germany vs Italy</li>
                    </ul>
    
                    <h4>Phase Lengths</h4>
                    <ul>
                        <li>1+ hour/phase</li>
                        <li>5 minute/phase - 30 minute/phase</li>
                    </ul>
                </p>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="javascript/locales/ghostRatings.js"></script>

<?php
libHTML::footer();
?>
