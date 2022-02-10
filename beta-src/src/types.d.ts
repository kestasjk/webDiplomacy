export type navIconProps = {
  iconState?: "active" | "inactive";
};

export interface gameIconProps {
  country:
    | "France"
    | "Austria"
    | "England"
    | "Germany"
    | "Russia"
    | "Italy"
    | "Turkey";
  iconState?:
    | "none"
    | "selected"
    | "hold"
    | "disbanded"
    | "dislodged"
    | "build";
}
