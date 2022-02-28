import { createTheme } from "@mui/material/styles";
import Country from "./enums/Country";

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

declare module "@mui/material/styles" {
  interface Palette {
    France: CountryColor;
    Austria: CountryColor;
    England: CountryColor;
    Germany: CountryColor;
    Russia: CountryColor;
    Italy: CountryColor;
    Turkey: CountryColor;
  }
}

/**
 * constants
 * ===
 * define constants that are to be re-used in the theme. use generic names that
 * describe their usage.
 */
const activeButtonStyle = {
  backgroundColor: "#fff",
  boxShadow: "0 0 2px 2px #000",
  color: "#000",
};
const boldFontWeight = 700;
const disabledBackground = "#b8b8b8";
const disabledText = "#cacaca";
const disabledBackgroundSecondary = "transparent";
const disabledTextSecondary = "#bababa";
const defaultLineHeight = 1.2;
const normalFontWeight = 400;

/// Interface and type for Country Color

interface CountryColor {
  main: string;
  light: string;
}

type CountryPaletteOptions = {
  [key in Country]: CountryColor;
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
      tablet: 834,
      tabletLandscape: 1024,
      desktop: 1500,
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
            ...activeButtonStyle,
          },
          "&:hover": {
            backgroundColor: "#757575",
            color: "#fff",
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
      contrastText: "#fff",
    },
    primary: {
      main: "#000",
      contrastText: "#fff",
    },
    secondary: {
      main: "#fff",
      contrastText: "#000",
    },
    ...countryPalette,
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
