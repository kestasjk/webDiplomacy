import { createTheme, SimplePaletteColorOptions } from "@mui/material/styles";
import Country from "./enums/Country";
import ArrowType from "./enums/ArrowType";

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
    arrowColors: {
      moveOrderSelected: SimplePaletteColorOptions;
      move: SimplePaletteColorOptions;
      convoy: SimplePaletteColorOptions;
      moveFailed: SimplePaletteColorOptions;
      moveSupport: SimplePaletteColorOptions;
      holdSupport: SimplePaletteColorOptions;
      retreat: SimplePaletteColorOptions;
      implied: SimplePaletteColorOptions;
      foreignImplied: SimplePaletteColorOptions;
    };
  }
}

declare module "@mui/material/styles/createPalette" {
  export interface PaletteOptions {
    svg: svgOptions;
    arrowColors: {
      moveOrderSelected: SimplePaletteColorOptions;
      move: SimplePaletteColorOptions;
      convoy: SimplePaletteColorOptions;
      moveFailed: SimplePaletteColorOptions;
      moveSupport: SimplePaletteColorOptions;
      holdSupport: SimplePaletteColorOptions;
      retreat: SimplePaletteColorOptions;
      implied: SimplePaletteColorOptions;
      foreignImplied: SimplePaletteColorOptions;
    };
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
    main: "#E136EA",
    light: "#F5BCF8",
  },
  Germany: {
    main: "#F37C0E",
    light: "#FBD3AF",
  },
  Russia: {
    main: "#3F1BC1",
    light: "#BFB3EA",
  },
  Italy: {
    main: "#47D2A0",
    light: "#C2F0DF",
  },
  Turkey: {
    main: "#F3C400",
    light: "#FBEBAA",
  },
};

type ArrowColors = {
  [key in ArrowType]: SimplePaletteColorOptions;
};

const arrowColors: ArrowColors = {
  moveOrderSelected: { main: "#FFFFFF" },
  move: { main: "#000000" },
  convoy: { main: "#2042B8" },
  moveFailed: { main: "#BB0000" },
  moveSupport: { main: "#F8F83D" },
  holdSupport: { main: "#3FC621" },
  retreat: { main: "#BD2894" },
  implied: { main: "#989898" },
  foreignImplied: { main: "rgba(0,0,0,.3)" },
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
