/* eslint-disable react/jsx-props-no-spreading */
import React, { InputHTMLAttributes } from "react";
import { useField } from "formik";
import { omit } from "lodash";

interface CheckboxProps extends InputHTMLAttributes<HTMLInputElement> {
  label: string;
  description?: string;
}

function Checkbox({ label, description, ...rest }: CheckboxProps) {
  const [field, { error, touched }] = useField({
    name: rest.name || "",
    type: rest.type,
  });

  return (
    <div className={`relative flex items-start ${rest.className}`}>
      <div className="flex items-center h-5">
        <input
          id={rest.name}
          type="checkbox"
          className="focus:ring-white h-5 w-5 text-black border-gray-300 rounded-full cursor-pointer"
          {...field}
          {...omit(rest, ["className"])}
        />
      </div>
      <div className="ml-3 text-sm">
        <label
          htmlFor={rest.name}
          className={`${description ? "font-medium" : ""} ${
            field.value ? "font-bold" : ""
          } text-gray-700 cursor-pointer text-md`}
        >
          {label}
        </label>
        {description && <p className="text-gray-500">{description}</p>}
      </div>
      {error && touched && (
        <p className="mt-2 text-sm text-red-600" id="email-error">
          {error}
        </p>
      )}
    </div>
  );
}

Checkbox.defaultProps = {
  description: "",
};

export default Checkbox;
