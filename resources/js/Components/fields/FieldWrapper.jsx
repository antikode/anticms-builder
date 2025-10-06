import { Label } from "../ui/label";

export default function FieldWrapper({
  id,
  children,
  label,
  caption,
  required,
  error,
}) {
  return (
    <div className="space-y-2">
      {label && (
        <div className="flex flex-row items-center">
          <Label htmlFor={id}>{label}</Label>
          {required && <span className="text-destructive">&nbsp;*</span>}
        </div>
      )}
      {children}
      {caption && !error && (
        <p className="text-[10px] text-muted-foreground">{caption}</p>
      )}
      {error && <p className="text-sm text-destructive">{error}</p>}
    </div>
  );
}