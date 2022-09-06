import React, { ReactElement, FunctionComponent, useEffect } from "react";
import { useFormik, FormikProvider } from "formik";
import useLocalStorageState from "use-local-storage-state";
import WDVerticalScroll from "./WDVerticalScroll";
import Checkbox from "./form/Checkbox";

interface SettingsProps {
  autoSave: boolean;
}

const WDHelp: FunctionComponent = function (): ReactElement {
  const [settings, setSettings] = useLocalStorageState<SettingsProps>(
    "settings",
    { defaultValue: { autoSave: true } },
  );

  const form = useFormik({
    initialValues: settings,
    onSubmit: (values: SettingsProps) => {
      setSettings(values);
    },
  });

  useEffect(() => {
    if (form.values !== form.initialValues) {
      form.submitForm();
    }
  }, [form.values]);

  return (
    <WDVerticalScroll>
      <FormikProvider value={form}>
        <div className="mt-3 px-3 sm:px-4">
          <div className="text-xs">Automatically Save Game:</div>
          <Checkbox
            name="autoSave"
            label={`Auto-Save ${form.values.autoSave ? "On" : "Off"}`}
            checked={form.values.autoSave}
            className="mt-3"
          />
        </div>
      </FormikProvider>
    </WDVerticalScroll>
  );
};

export default WDHelp;
