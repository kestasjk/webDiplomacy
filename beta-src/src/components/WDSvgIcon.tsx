import * as React from "react";
import { SvgIcon, SvgIconProps } from "@mui/material";

interface iconProps {
  component: React.FC;
}

type svgProps = iconProps & SvgIconProps;

const WDSvgIcon: React.FC<svgProps> = function (props) {
  const { component, style } = props;
  return <SvgIcon component={component} style={style} inheritViewBox />;
};

export default WDSvgIcon;
