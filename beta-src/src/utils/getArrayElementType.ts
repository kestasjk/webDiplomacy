type GetArrayElementType<T extends readonly string[]> =
  T extends readonly (infer U)[] ? U : never;

export default GetArrayElementType;
