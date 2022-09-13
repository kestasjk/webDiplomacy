/* eslint-disable require-jsdoc */
import React, { useCallback } from "react";
import useLocalStorageState from "use-local-storage-state";

interface Settings {
  lastPhaseClicked: number;
}

const useSettings = () => {
  const [settings, setSettings] = useLocalStorageState<Settings>("settings-2", {
    defaultValue: { lastPhaseClicked: 0 },
  });

  const setSetting = useCallback((key: string, value: string | number) => {
    const newSettings = { ...settings };
    newSettings[key] = value;
    setSettings(newSettings);
  }, []);

  return {
    settings,
    setSetting,
  };
};

export default useSettings;
