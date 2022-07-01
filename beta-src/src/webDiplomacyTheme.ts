import { createTheme, SimplePaletteColorOptions } from "@mui/material/styles";
import Country from "./enums/Country";
import ArrowColor from "./enums/ArrowColor";

declare module "@mui/material/styles" {
  interface BreakpointOverrides {
    xs: false;
    sm: false;
    md: false;
    lg: false;
    xl: false;
    mobile: true;
    mobileLandscape: true;
    mobileLg: true;
    mobileLgLandscape: true;
    tablet: true;
    tabletLandscape: true;
    desktop: true;
  }

  interface TypographyVariants {
    label: React.CSSProperties;
  }

  export interface TypographyVariantsOptions {
    label?: React.CSSProperties;
  }
}

declare module "@mui/material/Typography" {
  interface TypographyPropsVariantOverrides {
    label: true;
  }
}

interface svgOptions {
  filters: {
    dropShadows: string[];
  };
}

interface PaletteArrowColors {
  [ArrowColor.CONVOY]: SimplePaletteColorOptions;
  [ArrowColor.IMPLIED]: SimplePaletteColorOptions;
  [ArrowColor.IMPLIED_FOREIGN]: SimplePaletteColorOptions;
  [ArrowColor.MOVE]: SimplePaletteColorOptions;
  [ArrowColor.MOVE_FAILED]: SimplePaletteColorOptions;
  [ArrowColor.RETREAT]: SimplePaletteColorOptions;
  [ArrowColor.SUPPORT_HOLD]: SimplePaletteColorOptions;
  [ArrowColor.SUPPORT_MOVE]: SimplePaletteColorOptions;
  [ArrowColor.SUPPORT_HOLD_FAILED]: SimplePaletteColorOptions;
  [ArrowColor.SUPPORT_MOVE_FAILED]: SimplePaletteColorOptions;
}

declare module "@mui/material/styles" {
  interface Palette {
    France: SimplePaletteColorOptions;
    Austria: SimplePaletteColorOptions;
    England: SimplePaletteColorOptions;
    Germany: SimplePaletteColorOptions;
    Russia: SimplePaletteColorOptions;
    Italy: SimplePaletteColorOptions;
    Turkey: SimplePaletteColorOptions;
    svg: svgOptions;
    arrowColors: PaletteArrowColors;
  }
}

declare module "@mui/material/styles/createPalette" {
  export interface PaletteOptions {
    svg: svgOptions;
    arrowColors: PaletteArrowColors;
  }
}

/**
 * constants
 * ===
 * define constants that are to be re-used in the theme. use generic names that
 * describe their usage.
 */
const mainColor = "#000";
const secondaryColor = "#fff";
const activeButtonStyle = {
  backgroundColor: secondaryColor,
  boxShadow: "0 0 2px 2px",
  color: mainColor,
};

const focusButtonStyle = {
  backgroundColor: mainColor,
  boxShadow: "0 0 2px 2px",
  color: secondaryColor,
};
const boldFontWeight = 700;
const disabledBackground = "#b8b8b8";
const disabledText = "#cacaca";
const disabledBackgroundSecondary = "transparent";
const disabledTextSecondary = "#bababa";
const defaultLineHeight = 1.2;
const normalFontWeight = 400;

type CountryPaletteOptions = {
  [key in Country]: SimplePaletteColorOptions;
};

const countryPalette: CountryPaletteOptions = {
  France: {
    main: "#2D5EE8",
    light: "#B9C9F7",
  },
  Austria: {
    main: "#FC4343",
    light: "#FEC0C0",
  },
  England: {
    main: "#F146FA",
    light: "#F9C0FC",
  },
  Germany: {
    main: "#916620",
    light: "#E3C5A7",
  },
  Russia: {
    main: "#4B1BA2",
    light: "#C5B3E5",
  },
  Italy: {
    main: "#47D290",
    light: "#C2F0CF",
  },
  Turkey: {
    main: "#F6C903",
    light: "#FDEFAC",
  },
};

type ArrowColors = {
  [key in ArrowColor]: SimplePaletteColorOptions;
};

const arrowColors: ArrowColors = {
  [ArrowColor.CONVOY]: { main: "#2042B8" },
  [ArrowColor.IMPLIED]: { main: "#989898" },
  [ArrowColor.IMPLIED_FOREIGN]: { main: "rgba(0,0,0,.3)" },
  [ArrowColor.MOVE]: { main: "#000000" },
  [ArrowColor.MOVE_FAILED]: { main: "#BB0000" },
  [ArrowColor.RETREAT]: { main: "#BD2894" },
  [ArrowColor.SUPPORT_HOLD]: { main: "#000000" },
  [ArrowColor.SUPPORT_MOVE]: { main: "#000000" },
  [ArrowColor.SUPPORT_HOLD_FAILED]: { main: "#BB0000" },
  [ArrowColor.SUPPORT_MOVE_FAILED]: { main: "#BB0000" },
  [ArrowColor.CONVOY_FAILED]: { main: "#BB0000" },
};

/**
 * theme creation
 */
const webDiplomacyTheme = createTheme({
  breakpoints: {
    values: {
      mobile: 0,
      mobileLandscape: 600,
      mobileLg: 414,
      mobileLgLandscape: 896,
      tablet: 820,
      tabletLandscape: 1024,
      desktop: 1200,
    },
  },
  components: {
    MuiUseMediaQuery: {
      defaultProps: {
        noSsr: true,
      },
    },
    MuiButton: {
      styleOverrides: {
        root: {
          borderRadius: 18,
          padding: "10px 18px",
        },
        containedPrimary: {
          "&:active": {
            ...activeButtonStyle,
          },
          "&:focus": {
            ...focusButtonStyle,
          },
          "&:hover": {
            backgroundColor: "#757575",
            color: secondaryColor,
          },
        },
        containedSecondary: {
          "&.Mui-disabled": {
            backgroundColor: disabledBackgroundSecondary,
            color: disabledTextSecondary,
          },
          "&:active": {
            ...activeButtonStyle,
          },
          "&:focus": {
            ...activeButtonStyle,
          },
          "&:hover": {
            backgroundColor: "#fafafa",
          },
        },
      },
    },
  },
  palette: {
    action: {
      disabledBackground,
      disabled: disabledText,
    },
    error: {
      main: "#f00",
      contrastText: secondaryColor,
    },
    primary: {
      main: mainColor,
      contrastText: secondaryColor,
    },
    secondary: {
      main: secondaryColor,
      contrastText: mainColor,
    },
    ...countryPalette,
    arrowColors,
    svg: {
      filters: {
        dropShadows: [
          "drop-shadow(0px 3px 10px rgba(0, 0, 0, 0.7))",
          "drop-shadow(1px 4px 4px #323232)",
        ],
      },
    },
  },
  typography: {
    button: {
      fontSize: 12,
      fontWeight: boldFontWeight,
      lineHeight: defaultLineHeight,
      textTransform: "none",
    },
  },
});

/**
 * responsive overrides
 */
webDiplomacyTheme.typography.body1 = {
  fontFamily: webDiplomacyTheme.typography.fontFamily,
  fontSize: 14,
  fontWeight: normalFontWeight,
  lineHeight: defaultLineHeight,
};

webDiplomacyTheme.typography.h1 = {
  fontFamily: webDiplomacyTheme.typography.fontFamily,
  fontSize: 16,
  fontWeight: boldFontWeight,
  lineHeight: defaultLineHeight,
  [webDiplomacyTheme.breakpoints.up("desktop")]: {
    fontSize: 20,
  },
};

webDiplomacyTheme.typography.h2 = {
  fontFamily: webDiplomacyTheme.typography.fontFamily,
  fontSize: 14,
  fontWeight: boldFontWeight,
  lineHeight: defaultLineHeight,
};

webDiplomacyTheme.typography.label = {
  fontFamily: webDiplomacyTheme.typography.fontFamily,
  fontSize: 10,
  fontWeight: normalFontWeight,
  lineHeight: 1,
};

export default webDiplomacyTheme;
