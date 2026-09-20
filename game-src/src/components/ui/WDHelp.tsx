import React, {
  Fragment,
  ReactElement,
  FunctionComponent,
  useState,
  MouseEvent,
} from "react";
import WDVerticalScroll from "./WDVerticalScroll";
import Move from "../../assets/help/move.gif";
import Convoy from "../../assets/help/convoy.gif";
import Hold from "../../assets/help/hold.gif";
import SupportHold from "../../assets/help/support-hold.gif";
import SupportMove from "../../assets/help/support-move.gif";
import Retreat from "../../assets/help/retreat.gif";
import Build from "../../assets/help/build.gif";
import ViaConvoy from "../../assets/help/via-convoy.gif";

import { ReactComponent as BtnArrowIcon } from "../../assets/svg/btnArrow.svg";

interface itemProps {
  title: string;
  image: any;
  description: string;
  shortcutKey: string;
}

interface HelpProps {
  gameID: number;
}

const items: itemProps[] = [
  {
    title: "Move",
    image: Move,
    description:
      "Select Unit (or territory), select Move, select Destination Territory.",
    shortcutKey: "m or a",
  },
  {
    title: "Convoy",
    image: Convoy,
    description:
      "Select Army (or territory), select Convoy, select Destination Territory.",
    shortcutKey: "c",
  },
  {
    title: "Via Convoy",
    image: ViaConvoy,
    description:
      "Select Army (or territory), select Via Convoy, select Destination Territory.",
    shortcutKey: "v",
  },
  {
    title: "Hold",
    image: Hold,
    description: "Select Unit or territory, select Hold.",
    shortcutKey: "h or d",
  },
  {
    title: "Support Hold",
    image: SupportHold,
    description:
      "Select Supporting Unit (or territory), select Support, select Unit (or territory) to Support.",
    shortcutKey: "",
  },
  {
    title: "Support Move",
    image: SupportMove,
    description:
      "Select Supporting Unit (or territory), select Support, select Unit (or territory) to Support, select Destination Territory.",
    shortcutKey: "s",
  },
  {
    title: "Retreat",
    image: Retreat,
    description:
      "Select Unit (or territory), select Retreat, select Destination Territory.",
    shortcutKey: "",
  },
  {
    title: "Build",
    image: Build,
    description:
      "Select supply center territory, select type of unit (Army or Fleet).",
    shortcutKey: "",
  },
];

const WDHelp: FunctionComponent<HelpProps> = function ({
  gameID,
}): ReactElement {
  const [currentIndex, setCurrentIndex] = useState<number>(0);
  const modForumLink = `/modforum.php?fromGameID=${gameID}`;
  const suspicionLink = `/board.php?gameID=${gameID}&view=dropDown&lodgeSuspicion=on`;
  return (
    <WDVerticalScroll>
      <div className="mt-3 px-3 sm:px-4">
        <div className="text-lg font-bold">Using the UI:</div>
        {items.map((item: itemProps, index: number) => (
          <Fragment key={item.title}>
            {currentIndex === index && (
              <>
                <div
                  className="w-full h-48 bg-cover bg-center rounded-2xl"
                  style={{ backgroundImage: `url(${item.image})` }}
                />
                <div className="flex mt-4 mb-2">
                  <div className="flex-1 text-lg font-bold">{item.title}</div>
                  <div className="flex">
                    <button
                      className="mr-1"
                      type="button"
                      onClick={(event: MouseEvent<HTMLButtonElement>) => {
                        event.stopPropagation();
                        if (index > 0) {
                          setCurrentIndex(index - 1);
                        }
                      }}
                    >
                      <BtnArrowIcon
                        className={`text-black stroke-white ${
                          index === 0 && "text-gray-300 cursor-not-allowed"
                        }`}
                      />
                    </button>
                    <button
                      type="button"
                      onClick={(event: MouseEvent<HTMLButtonElement>) => {
                        event.stopPropagation();
                        if (index < items.length - 1) {
                          setCurrentIndex(index + 1);
                        }
                      }}
                    >
                      <BtnArrowIcon
                        className={`scale-y-[-1] text-black stroke-white ${
                          index === items.length - 1 &&
                          "text-gray-300 cursor-not-allowed"
                        }`}
                      />
                    </button>
                  </div>
                </div>
                <div>
                  {item.description}{" "}
                  {item.shortcutKey && (
                    <span>
                      Shortcut Key:{" "}
                      <span className="font-bold">{item.shortcutKey}</span>
                    </span>
                  )}
                </div>
              </>
            )}
          </Fragment>
        ))}
      </div>
      <div className="mt-3 px-3 sm:px-4">
        <hr />
        <div className="mt-4 mb-2">
          <div className="text-lg font-bold">Helpful information:</div>
          <div>
            <ul>
              <li>
                For a summary of the rules of the game:
                <br />
                <a href="intro.php" className="text-blue-500">
                  <b>An intro to Diplomacy</b>
                  <br />
                  <br />
                </a>
              </li>
              <li>
                Before asking a general question, check if it has already been
                asked:
                <br />
                <a href="faq.php" className="text-blue-500">
                  <b>Frequently asked questions</b>
                  <br />
                  <br />
                </a>
              </li>
              <li>
                To check whether something breaks the site rules:
                <br />
                <a href="rules.php" className="text-blue-500">
                  <b>Site rules</b>
                  <br />
                  <br />
                </a>
              </li>
              <li>
                To find out how points and scoring works:
                <br />
                <a href="points.php" className="text-blue-500">
                  <b>Guide to points / scoring</b>
                  <br />
                  <br />
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div className="mt-3 px-3 sm:px-4">
        <hr />
        <div className="mt-4 mb-2">
          <div className="text-lg font-bold">Requesting help:</div>
          <div>
            <ul>
              <li>
                If you have a reason to suspect other player(s) of cheating /
                rule breaking / collusion:
                <br />
                <a href={suspicionLink} className="text-blue-500">
                  <b>Lodge a cheating suspicion</b>
                  <br />
                  <br />
                </a>
              </li>
              <li>
                For any other issue relating to this game that requires a
                moderator:
                <br />
                <a href={modForumLink} className="text-blue-500">
                  <b>Lodge a moderator forum ticket</b>
                  <br />
                  <br />
                </a>
              </li>
              <li>
                For advice, help with the interface or rules, bug reports,
                questions on the rules, etc:
                <br />
                <a href="contrib/phpBB3/" className="text-blue-500">
                  <b>Go to the forum</b>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </WDVerticalScroll>
  );
};

export default WDHelp;
