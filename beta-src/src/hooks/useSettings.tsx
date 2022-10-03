/* eslint-disable require-jsdoc */
import React, { useCallback } from "react";
import useLocalStorageState from "use-local-storage-state";

interface Settings {
  autoSave: boolean;
}

const useSettings = () => {
  const [settings, setSettings] = useLocalStorageState<Settings>("settings", {
    defaultValue: { autoSave: true },
  });

  const setSetting = useCallback(
    (key: string, value: string | number | boolean) => {
      const newSettings = { ...settings };
      newSettings[key] = value;
      setSettings(newSettings);
    },
    [],
  );

  return {
    settings,
    setSetting,
  };
};

export default useSettings;
