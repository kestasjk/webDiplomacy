import React, { FunctionComponent, ReactElement } from "react";
import useSettings from "../../../../hooks/useSettings";

import { ReactComponent as CheckmarkIcon } from "../../../../assets/svg/checkmark.svg";

interface AutoSaveToggleProps {
  className?: string;
}

const AutoSaveToggle: FunctionComponent<AutoSaveToggleProps> = function ({
  className,
}): ReactElement {
  // This is here because I have the feeling that there is not a consensus about the auto-save feature yet.
  // We might have to have this in the back-end
  // const [settings, setSettings] = useLocalStorageState("settings", {
  //   defaultValue: { autoSave: true },
  // });
  const { settings, setSetting } = useSettings();

  return (
    <div className={className}>
      <button
        onClick={() => setSetting("autoSave", !settings.autoSave)}
        type="button"
        className="w-full"
      >
        <div className="flex">
          <div
            className={`w-6 h-6 rounded-full border-2 border-black mr-1 ${
              settings.autoSave ? "bg-black" : "bg-white"
            }`}
          >
            <CheckmarkIcon className="text-white w-[85%] h-[85%] pl-[4px] pt-[3px]" />
          </div>
          <div className="bg-black text-white font-medium uppercase w-fit text-sm py-0.5 px-3 rounded-[4px]">
            auto save: {settings.autoSave ? "on" : "off"}
          </div>
        </div>
      </button>
    </div>
  );
};

AutoSaveToggle.defaultProps = { className: "" };

export default AutoSaveToggle;
