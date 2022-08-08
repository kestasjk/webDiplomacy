import React, { FunctionComponent, ReactElement } from "react";

interface RightButtonProps {
  image: string;
  text: string;
  onClick: () => void;
  className?: string;
}

const RightButton: FunctionComponent<RightButtonProps> = function ({
  image,
  text,
  onClick,
  className,
}): ReactElement {
  return (
    <div className={className}>
      <button onClick={onClick} type="button" className="w-full">
        <img
          src={`beta/images/icons/${image}Button.svg`}
          alt="action"
          className="mx-auto"
        />
      </button>
      <div className="bg-black uppercase text-white text-center py-0.5 w-full text-xs font-bold rounded-md">
        {text}
      </div>
    </div>
  );
};

RightButton.defaultProps = { className: "" };

export default RightButton;
