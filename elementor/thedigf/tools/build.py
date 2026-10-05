"""Builds the TheDIGF Elementor global kit + page templates from the HTML design."""
import json, random, os, zipfile, copy

OUT = os.environ.get("OUT", os.path.join(os.path.dirname(os.path.abspath(__file__)), ".."))
random.seed(20261005)
_used = set()


def uid():
    while True:
        v = "%07x" % random.getrandbits(28)
        if v not in _used:
            _used.add(v)
            return v


# ---------------------------------------------------------------- value helpers
def sz(n, unit="px"):
    return {"unit": unit, "size": n, "sizes": []}


def dim(t, r=None, b=None, l=None, unit="px"):
    if r is None:
        r = b = l = t
    elif b is None:
        b, l = t, r
    linked = t == r == b == l
    return {"unit": unit, "top": str(t), "right": str(r), "bottom": str(b), "left": str(l), "isLinked": linked}


def gap(col, row=None):
    row = col if row is None else row
    return {"column": str(col), "row": str(row), "isLinked": col == row, "unit": "px", "size": col}


def C(cid):  # global colour reference
    return "globals/colors?id=" + cid


def T(tid):  # global typography reference
    return "globals/typography?id=" + tid


def link(url, external=False):
    return {"url": url, "is_external": "on" if external else "", "nofollow": "", "custom_attributes": ""}


def icon(value, lib="fa-solid"):
    return {"value": value, "library": lib}


# ---------------------------------------------------------------- global kit
COLORS = [
    # (id, title, hex)
    ("dgnavy", "Navy", "#102E43"),
    ("dgdeep", "Deep Navy", "#092235"),
    ("dgnight", "Night (Hero / Header)", "#071E30"),
    ("dgink", "Ink (Headings)", "#142E40"),
    ("dgmuted", "Muted Text", "#5D6E78"),
    ("dggold", "Gold", "#D5AE65"),
    ("dggoldlt", "Gold Light (CTA)", "#DFBD7A"),
    ("dggoldhv", "Gold Hover", "#EED2A0"),
    ("dggolddk", "Gold Dark (Eyebrow)", "#8C6B34"),
    ("dgcream", "Cream (Page BG)", "#F7F5EF"),
    ("dgsand", "Sand", "#E8E1D2"),
    ("dgstone", "Stone (Callout BG)", "#EEECE5"),
    ("dgline", "Line / Border", "#D9DFDF"),
    ("dgpaper", "Paper", "#F5F7F7"),
    ("dgwhite", "White", "#FFFFFF"),
    ("dgondark", "Text on Dark", "#B9CBD7"),
    ("dgonhero", "Lead on Dark", "#E0EAF0"),
    ("dgnavyhv", "Navy Hover", "#244D65"),
    ("dggreen", "Status Green", "#30896F"),
    ("dgyellow", "Status Yellow", "#C99735"),
    ("dgred", "Status Red", "#BC5C5E"),
]
HEX = {c[0]: c[2] for c in COLORS}

SYSTEM_COLORS = [
    {"_id": "primary", "title": "Primary", "color": "#142E40"},
    {"_id": "secondary", "title": "Secondary", "color": "#D5AE65"},
    {"_id": "text", "title": "Text", "color": "#5D6E78"},
    {"_id": "accent", "title": "Accent", "color": "#102E43"},
]


def typo(_id, title, size, size_t=None, size_m=None, weight="400", lh=1.65, ls=None, transform=None, family="Inter"):
    t = {
        "_id": _id,
        "title": title,
        "typography_typography": "custom",
        "typography_font_family": family,
        "typography_font_weight": weight,
        "typography_font_size": sz(size),
        "typography_line_height": sz(lh, "em"),
    }
    if size_t is not None:
        t["typography_font_size_tablet"] = sz(size_t)
    if size_m is not None:
        t["typography_font_size_mobile"] = sz(size_m)
    if ls is not None:
        t["typography_letter_spacing"] = sz(ls)
    if transform:
        t["typography_text_transform"] = transform
    return t


SYSTEM_TYPO = [
    typo("primary", "Primary", 50, 40, 34, "600", 1.14, -2.5),
    typo("secondary", "Secondary", 18, None, 16, "600", 1.3, -0.3),
    typo("text", "Text", 16, None, None, "400", 1.65),
    typo("accent", "Accent", 14, None, 13, "600", 1, -0.2),
]
CUSTOM_TYPO = [
    typo("dgh1", "H1 Display", 66, 52, 40, "600", 1.1, -3.4),
    typo("dgh2", "H2 Section", 50, 40, 34, "600", 1.14, -2.5),
    typo("dgh3", "H3 Card", 18, 18, 16, "600", 1.3, -0.3),
    typo("dgh3lg", "H3 Large", 30, 28, 26, "600", 1.14, -1.4),
    typo("dgeyebrow", "Eyebrow", 11.5, None, 10.5, "600", 1.4, 1.4, "uppercase"),
    typo("dglead", "Lead", 17.5, None, 16, "400", 1.6),
    typo("dgintro", "Intro / Body", 16, None, 15, "400", 1.8),
    typo("dgsmall", "Small", 14.5, None, 14, "400", 1.7),
    typo("dgxs", "Extra Small", 12.5, None, 12, "400", 1.6),
    typo("dgstat", "Stat Number", 29, None, 24, "600", 1.1, -1.4),
    typo("dgprice", "Price", 27, 24, 24, "600", 1.15, -1.3),
    typo("dgbrand", "Brand / Logo", 25.6, None, 21.6, "700", 1.2, -1.2),
    typo("dgbutton", "Button", 14, None, 13, "600", 1, -0.2),
    typo("dgemph", "Emphasis", 14, None, 14, "500", 1.6),
]


def kit_settings():
    s = {
        "system_colors": SYSTEM_COLORS,
        "custom_colors": [{"_id": i, "title": t, "color": h} for i, t, h in COLORS],
        "system_typography": SYSTEM_TYPO,
        "custom_typography": CUSTOM_TYPO,
        "default_generic_fonts": "Sans-serif",
        # Layout
        "container_width": sz(1220),
        "container_padding": dim(0),
        "space_between_widgets": {"column": "20", "row": "20", "isLinked": True, "unit": "px"},
        "page_title_selector": "h1.entry-title",
        # Background
        "body_background_background": "classic",
        "body_background_color": HEX["dgcream"],
        # Theme style: body + links + headings
        "body_color": HEX["dgink"],
        "body_typography_typography": "custom",
        "body_typography_font_family": "Inter",
        "body_typography_font_size": sz(16),
        "body_typography_font_weight": "400",
        "body_typography_line_height": sz(1.65, "em"),
        "link_normal_color": HEX["dgink"],
        "link_hover_color": HEX["dggolddk"],
        # Buttons (default = navy CTA)
        "button_typography_typography": "custom",
        "button_typography_font_family": "Inter",
        "button_typography_font_size": sz(14),
        "button_typography_font_weight": "600",
        "button_typography_line_height": sz(1, "em"),
        "button_typography_letter_spacing": sz(-0.2),
        "button_text_color": "#FFFFFF",
        "button_background_background": "classic",
        "button_background_color": HEX["dgnavy"],
        "button_hover_text_color": "#FFFFFF",
        "button_hover_background_background": "classic",
        "button_hover_background_color": HEX["dgnavyhv"],
        "button_border_radius": dim(7),
        "button_padding": dim(17, 20),
        # Images
        "image_border_radius": dim(14),
        # Form fields
        "form_label_color": HEX["dgink"],
        "form_label_typography_typography": "custom",
        "form_label_typography_font_family": "Inter",
        "form_label_typography_font_size": sz(13),
        "form_label_typography_font_weight": "500",
        "form_field_typography_typography": "custom",
        "form_field_typography_font_family": "Inter",
        "form_field_typography_font_size": sz(14.5),
        "form_field_text_color": HEX["dgink"],
        "form_field_background_color": "#FAFBFA",
        "form_field_border_border": "solid",
        "form_field_border_width": dim(1),
        "form_field_border_color": "#D3DCDC",
        "form_field_border_radius": dim(6),
        "form_field_padding": dim(10, 12),
        "form_field_focus_background_color": "#FFFFFF",
        "form_field_focus_border_border": "solid",
        "form_field_focus_border_width": dim(1),
        "form_field_focus_border_color": "#AF8C50",
    }
    for lvl, (size, size_t, size_m) in {
        "h1": (66, 52, 40),
        "h2": (50, 40, 34),
        "h3": (18, 18, 16),
        "h4": (16, 16, 15),
        "h5": (15, 15, 14),
        "h6": (14, 14, 13),
    }.items():
        s[f"{lvl}_color"] = HEX["dgink"]
        s[f"{lvl}_typography_typography"] = "custom"
        s[f"{lvl}_typography_font_family"] = "Inter"
        s[f"{lvl}_typography_font_weight"] = "600"
        s[f"{lvl}_typography_font_size"] = sz(size)
        s[f"{lvl}_typography_font_size_tablet"] = sz(size_t)
        s[f"{lvl}_typography_font_size_mobile"] = sz(size_m)
        s[f"{lvl}_typography_line_height"] = sz(1.14, "em")
    return s


# ---------------------------------------------------------------- element helpers
def con(children=None, **s):
    """Flexbox container. Defaults: full width child, column direction."""
    s.setdefault("content_width", "full")
    return {"id": uid(), "elType": "container", "isInner": False, "settings": s, "elements": children or []}


def row(children, g=20, align="center", justify=None, wrap=None, mobile_col=True, **s):
    s.update(flex_direction="row", flex_gap=gap(g), flex_align_items=align)
    if justify:
        s["flex_justify_content"] = justify
    if wrap:
        s["flex_wrap"] = wrap
    if mobile_col:
        s["flex_direction_mobile"] = "column"
        s["flex_align_items_mobile"] = "stretch"
    return con(children, **s)


def col(children, g=0, **s):
    s.update(flex_direction="column", flex_gap=gap(g))
    return con(children, **s)


def grid(children, cols, cols_t=None, cols_m=1, g=18, **s):
    s.update(
        container_type="grid",
        grid_columns_grid=sz(cols, "fr"),
        grid_columns_grid_tablet=sz(cols_t or cols, "fr"),
        grid_columns_grid_mobile=sz(cols_m, "fr"),
        grid_rows_grid=sz("auto", "custom"),
        grid_rows_grid_tablet=sz("auto", "custom"),
        grid_rows_grid_mobile=sz("auto", "custom"),
        grid_gaps=gap(g),
        grid_auto_flow="row",
    )
    return con(children, **s)


def section(children, bg=None, pt=84, pb=84, pt_m=48, pb_m=48, g=0, anchor=None, gradient=None, boxed=1220, **s):
    s.update(
        content_width="boxed",
        boxed_width=sz(boxed),
        flex_direction="column",
        flex_gap=gap(g),
        padding=dim(pt, 40, pb, 40),
        padding_tablet=dim(pt, 24, pb, 24),
        padding_mobile=dim(pt_m, 18, pb_m, 18),
    )
    if bg:
        s["background_background"] = "classic"
        s.setdefault("__globals__", {})["background_color"] = C(bg)
    if gradient:
        s.update(gradient)
    if anchor:
        s["_element_id"] = anchor
    return con(children, **s)


def widget(wtype, **s):
    return {"id": uid(), "elType": "widget", "widgetType": wtype, "isInner": False, "settings": s, "elements": []}


def heading(text, tag="h2", typ="dgh2", color="dgink", align=None, url=None, **s):
    s.update(title=text, header_size=tag)
    s.setdefault("__globals__", {}).update(typography_typography=T(typ), title_color=C(color))
    if align:
        s["align"] = align
    if url:
        s["link"] = link(url)
    return widget("heading", **s)


def eyebrow(text, color="dggolddk", **s):
    return heading(text, "div", "dgeyebrow", color, **s)


def text(html, typ="dgintro", color="dgmuted", spacing=0, **s):
    if not html.lstrip().startswith("<"):
        html = "<p>" + html + "</p>"
    s.update(editor=html, paragraph_spacing=sz(spacing))
    s.setdefault("__globals__", {}).update(typography_typography=T(typ), text_color=C(color))
    return widget("text-editor", **s)


def button(label, url="#discovery", style="navy", full=False, **s):
    s.update(text=label, link=link(url), border_radius=dim(7), text_padding=dim(17, 20))
    g = s.setdefault("__globals__", {})
    g["typography_typography"] = T("dgbutton")
    s["background_background"] = "classic"
    s["button_background_hover_background"] = "classic"
    if style == "gold":
        g.update(background_color=C("dggoldlt"), button_text_color=C("dgnavy"),
                 button_background_hover_color=C("dggoldhv"), hover_color=C("dgnavy"))
    else:
        g.update(background_color=C("dgnavy"), button_text_color=C("dgwhite"),
                 button_background_hover_color=C("dgnavyhv"), hover_color=C("dgwhite"))
    s["hover_animation"] = "float"
    if full:
        s["align"] = "justify"
    else:
        s["align_mobile"] = "justify"
    return widget("button", **s)


def text_link(label, url, color="dgink", **s):
    """Underlined text link, built with the Button widget."""
    s.update(
        text=label, link=link(url), text_padding=dim(0, 0, 4, 0), border_radius=dim(0),
        border_border="solid", border_width=dim(0, 0, 1, 0),
        background_background="classic", background_color="#00000000",
        button_background_hover_background="classic", button_background_hover_color="#00000000",
    )
    s.setdefault("__globals__", {}).update(
        typography_typography=T("dgemph"), button_text_color=C(color), border_color=C(color), hover_color=C("dggolddk"),
    )
    return widget("button", **s)


def image(url, alt, radius=14, caption=None, shadow=True, **s):
    s.update(image={"url": url, "id": "", "alt": alt, "source": "library"}, image_size="full",
             image_border_radius=dim(radius), width=sz(100, "%"))
    if shadow:
        s.update(image_box_shadow_box_shadow_type="yes",
                 image_box_shadow_box_shadow={"horizontal": 0, "vertical": 18, "blur": 45, "spread": 0, "color": "rgba(20,46,64,0.14)"})
    if caption:
        s.update(caption_source="custom", caption=caption, caption_align="left", caption_space=sz(14))
        s.setdefault("__globals__", {}).update(caption_typography_typography=T("dgsmall"), text_color=C("dgmuted"))
    return widget("image", **s)


def dot(color, size=8):
    return widget("icon", selected_icon=icon("fas fa-circle"), size=sz(size), align="left",
                  __globals__={"primary_color": C(color)})


def card(children, bg="dgwhite", border="dgline", radius=12, pad=(28, 30), pad_m=None, g=0, **s):
    s.update(flex_direction=s.pop("flex_direction", "column"), flex_gap=gap(g),
             padding=dim(pad[0], pad[1]), border_border="solid", border_width=dim(1),
             border_radius=dim(radius), background_background="classic")
    if pad_m:
        s["padding_mobile"] = dim(pad_m[0], pad_m[1])
    gl = s.setdefault("__globals__", {})
    gl["background_color"] = C(bg)
    if border.startswith("#") or border.startswith("rgba"):
        s["border_color"] = border
    else:
        gl["border_color"] = C(border)
    return con(children, **s)


def html(code, **s):
    s["html"] = code
    return widget("html", **s)


def accordion(items, numbered=False, content_pad=(0, 0, 23, 32)):
    """Nested Accordion (core widget) - items: list of (title, body_html).
    Gold index numbers (01, 02 ...) are added by the page CSS (class dg-acc-num)."""
    settings = {
        "items": [{"item_title": t, "_id": uid()} for t, _ in items],
        "_css_classes": "dg-acc" + (" dg-acc-num" if numbered else ""),
        "title_tag": "div",
        "default_state": "all_collapsed",
        "max_items_expended": "one",
        "faq_schema": "yes" if not numbered else "",
        "accordion_item_title_position_horizontal": "stretch",
        "accordion_item_title_icon_position": "end",
        "accordion_item_title_icon": icon("", ""),
        "accordion_item_title_icon_active": icon("", ""),
        "accordion_item_title_space_between": sz(0),
        "accordion_item_title_distance_from_content": sz(0),
        "accordion_padding": dim(22, 0, 22, 0),
        "accordion_border_radius": dim(0),
        "content_padding": dim(*content_pad),
        "icon_size": sz(14),
        "__globals__": {
            "title_typography_typography": T("dgemph"),
            "normal_title_color": C("dgink"), "hover_title_color": C("dggolddk"), "active_title_color": C("dgink"),
            "normal_icon_color": C("dggolddk"), "hover_icon_color": C("dggolddk"), "active_icon_color": C("dggolddk"),
        },
    }
    for st in ("normal", "hover", "active"):
        settings[f"accordion_background_{st}_background"] = "classic"
        settings[f"accordion_background_{st}_color"] = "#00000000"
        settings[f"accordion_border_{st}_border"] = "solid"
        settings[f"accordion_border_{st}_width"] = dim(0, 0, 1, 0)
        settings["__globals__"][f"accordion_border_{st}_color"] = C("dgline")
    children = [
        con([text(body, "dgsmall")], _title=f"item #{i + 1}", flex_direction="column", padding=dim(0),
            border_border="none")
        for i, (_, body) in enumerate(items)
    ]
    w = widget("nested-accordion", **settings)
    w["elements"] = children
    return w


def tabs(items):
    """Nested Tabs (core widget) - items: list of (tab_title, panel_title, panel_text)."""
    settings = {
        "tabs": [{"tab_title": t, "_id": uid()} for t, _, _ in items],
        "_css_classes": "dg-tabs",
        "tabs_direction": "block-start",
        "tabs_justify_horizontal": "stretch",
        "title_alignment": "start",
        "breakpoint_selector": "mobile",
        "tabs_title_space_between": sz(10),
        "tabs_title_spacing": sz(25),
        "padding": dim(15, 20),
        "tabs_title_border_radius": dim(8),
        "tabs_title_background_color_background": "classic",
        "tabs_title_background_color_color": "#00000000",
        "tabs_title_border_border": "solid",
        "tabs_title_border_width": dim(1),
        "tabs_title_background_color_hover_background": "classic",
        "tabs_title_border_hover_border": "solid",
        "tabs_title_border_hover_width": dim(1),
        "tabs_title_background_color_active_background": "classic",
        "tabs_title_border_active_border": "solid",
        "tabs_title_border_active_width": dim(1),
        "box_background_color_background": "classic",
        "box_border_border": "solid",
        "box_border_width": dim(1),
        "box_border_radius": dim(13),
        "__globals__": {
            "title_typography_typography": T("dgemph"),
            "title_text_color": C("dgmuted"), "title_text_color_hover": C("dgink"), "title_text_color_active": C("dgwhite"),
            "tabs_title_border_color": C("dgline"),
            "tabs_title_background_color_hover_color": C("dgwhite"), "tabs_title_border_hover_color": C("dggold"),
            "tabs_title_background_color_active_color": C("dgnavy"), "tabs_title_border_active_color": C("dgnavy"),
            "box_background_color_color": C("dgwhite"), "box_border_color": C("dgline"),
        },
    }
    children = []
    for i, (_, ptitle, ptext) in enumerate(items):
        children.append(con([
            col([heading(ptitle, "h3", "dgh3lg")], width=sz(40, "%"), width_mobile=sz(100, "%")),
            text(ptext, "dgintro"),
        ], _title=f"Tab #{i + 1}", flex_direction="row", flex_direction_mobile="column", flex_align_items="center",
            flex_align_items_mobile="stretch", flex_gap=gap(45), flex_gap_mobile=gap(17), padding=dim(35), padding_mobile=dim(24), min_height=sz(160),
            min_height_mobile=sz(0)))
    w = widget("nested-tabs", **settings)
    w["elements"] = children
    return w


# ---------------------------------------------------------------- page: HOME
IMG_HERO = "https://teletechtx.com/wp-content/uploads/2026/09/HD-Image-for-Front-of-TT-TX-website-1.png"
IMG_PROCESS = "https://teletechtx.com/wp-content/uploads/2026/09/Why-People-Choose-Us-HD-Image.png"
IMG_FOUNDER = "https://teletechtx.com/wp-content/uploads/2026/09/TT-TX-website-9.25.2026-768x994.png"
CTA = "Request Complimentary Discovery"

HERO_CANVAS = """<script>
/* Ambient hero motion (optional). Delete this HTML widget to remove the animation. */
(function(){var s=document.currentScript,hero=s&&s.closest('.dg-hero');if(!hero||hero.querySelector('canvas.dg-hero-motion'))return;
var c=document.createElement('canvas');c.className='dg-hero-motion';c.setAttribute('aria-hidden','true');
c.style.cssText='position:absolute;inset:0;width:100%;height:100%;z-index:-1;pointer-events:none;opacity:.55';
hero.style.isolation='isolate';hero.prepend(c);var x=c.getContext('2d');if(!x)return;
var rm=matchMedia('(prefers-reduced-motion: reduce)'),w=0,h=0,f=0,v=true,t=0,l=0;
function rs(){var r=hero.getBoundingClientRect();w=r.width;h=r.height;var p=Math.min(devicePixelRatio||1,1.5);c.width=Math.round(w*p);c.height=Math.round(h*p);x.setTransform(p,0,0,p,0,0);d()}
function d(){x.clearRect(0,0,w,h);for(var i=0;i<7;i++){var y=h*(.16+i*.115);x.beginPath();for(var X=-20;X<=w+20;X+=20){var Y=y+Math.sin(X/330+t*.16+i*.8)*24+Math.cos(X/620-t*.1+i)*16;X===-20?x.moveTo(X,Y):x.lineTo(X,Y)}x.strokeStyle=i%3===0?'rgba(224,188,121,.13)':'rgba(168,207,232,.1)';x.lineWidth=1;x.stroke()}
for(var j=0;j<9;j++){var px=((j*.137+t*.006)%1)*w,py=h*(.2+(j%5)*.135)+Math.sin(t*.2+j)*13;x.beginPath();x.arc(px,py,1.5,0,Math.PI*2);x.fillStyle='rgba(224,188,121,.3)';x.fill()}}
function tk(n){f=0;if(!v||document.hidden||rm.matches){l=0;return}if(l)t+=Math.min((n-l)/1000,.05);l=n;d();f=requestAnimationFrame(tk)}
function sy(){if(f)cancelAnimationFrame(f);f=0;l=0;if(v&&!document.hidden&&!rm.matches)f=requestAnimationFrame(tk);else d()}
new ResizeObserver(rs).observe(hero);new IntersectionObserver(function(e){v=e[0].isIntersecting;sy()}).observe(hero);
document.addEventListener('visibilitychange',sy);rs();sy()})();
</script>"""


PRIVACY_NOTE = "Your information is used only to schedule your discovery conversation. It is never sold or shared."

# Styles and behaviour Elementor (free) cannot set natively. Loaded once by an HTML widget in the hero.
# Widgets/containers carry the matching classes in Advanced > CSS Classes.
PAGE_CSS_JS = """<style>
/* Lifecycle tabs: equal-width tabs with a small "01 / OWNERSHIP" label above each title */
.elementor .dg-tabs .e-n-tabs{counter-reset:dgtab}
.elementor .dg-tabs .e-n-tab-title{flex:1 1 0;counter-increment:dgtab;justify-content:flex-start;text-align:left;line-height:1.4}
.elementor .dg-tabs .e-n-tab-title-text{display:block}
.elementor .dg-tabs .e-n-tab-title-text::before{content:counter(dgtab,decimal-leading-zero) " / OWNERSHIP";display:block;font-size:.61rem;font-weight:500;letter-spacing:.08em;margin-bottom:7px;color:#97743D}
.elementor .dg-tabs .e-n-tab-title[aria-selected="true"] .e-n-tab-title-text::before{color:#D5AE65}
/* Accordions: no box around the answer, rule above the first item, 16px titles, gold index numbers */
.elementor .dg-acc .e-n-accordion-item>.e-con{border:0!important}
.elementor .dg-acc .e-n-accordion-item:first-child>.e-n-accordion-item-title{border-top:1px solid #D9DFDF}
.elementor .dg-acc .e-n-accordion-item-title-text{font-size:1rem!important;font-weight:500!important}
.elementor .dg-acc-num .e-n-accordion{counter-reset:dgacc}
.elementor .dg-acc-num .e-n-accordion-item{counter-increment:dgacc}
.elementor .dg-acc-num .e-n-accordion-item-title-text::before{content:counter(dgacc,decimal-leading-zero);display:inline-block;min-width:32px;font-size:.73rem;font-weight:400;letter-spacing:.04em;color:#94723B}
/* Scorecard: filter bar, collapsible rows, status pills */
.elementor .dg-filters{display:flex;gap:8px;flex-wrap:wrap;background:#F5F7F7;padding:18px 30px;border-bottom:1px solid #D9DFDF}
.elementor .dg-filters .dg-filter{background:#fff;border:1px solid #D9DFDF;border-radius:6px;font-family:inherit;font-size:.78rem;font-weight:400;line-height:1.3;padding:9px 13px;color:#5D6E78;cursor:pointer;box-shadow:none;transform:none}
.elementor .dg-filters .dg-filter:hover{background:#fff;color:#142E40;border-color:#C6AA7B}
.elementor .dg-filters .dg-filter[aria-pressed="true"]{background:#102E43;color:#fff;border-color:#102E43}
.elementor .dg-score-row{cursor:pointer;transition:background-color .2s}
.elementor .dg-score-row:hover,.elementor .dg-score-row.is-open{background-color:#F7F8F5!important}
.elementor .dg-score-row .dg-finding{display:none}
.elementor .dg-score-row.is-open .dg-finding,.elementor-editor-active .dg-score-row .dg-finding{display:block}
.elementor .dg-score-row.is-hidden{display:none!important}
.elementor .dg-pill{flex:0 0 auto!important;width:auto!important;white-space:nowrap}
/* Pricing bullets in two columns */
.elementor .dg-tier-list .elementor-icon-list-items{display:grid!important;grid-template-columns:1fr 1fr;gap:13px 20px}
.elementor .dg-tier-list .elementor-icon-list-item{margin:0!important;padding:0!important}
/* Discovery section: smaller title, compact form fields */
.elementor .dg-request-title .elementor-heading-title{font-size:clamp(2rem,2.6vw,2.5rem)!important;line-height:1.16!important}
.elementor .dg-form .elementor-field-textual{min-height:42px;padding:8px 12px;font-size:.9rem}
.elementor .dg-form .elementor-field-type-html{font-size:.73rem;line-height:1.6;color:#5D6E78;padding-top:6px}
/* Reading progress bar and mobile sticky CTA */
.dg-progress{position:fixed;top:0;left:0;height:3px;width:0;background:#D5AE65;z-index:99999;pointer-events:none}
.dg-sticky-cta{display:none}
@media(max-width:767px){
 .elementor .dg-tier-list .elementor-icon-list-items{grid-template-columns:1fr}
 .elementor .dg-filters{padding:15px;gap:6px}
 .elementor .dg-filters .dg-filter{font-size:.68rem;padding:8px 10px}
 .elementor .dg-request-title .elementor-heading-title{font-size:2.3rem!important}
 .dg-sticky-cta.show{display:block;position:fixed;left:0;right:0;bottom:0;z-index:999;padding:10px 13px;background:rgba(247,245,239,.96);backdrop-filter:blur(10px);border-top:1px solid #D9DFDF}
 .dg-sticky-cta a{display:flex;align-items:center;justify-content:center;height:48px;border-radius:7px;background:#102E43;color:#fff!important;font:600 13px/1 Inter,sans-serif;text-decoration:none}
}
@media(max-width:400px){.elementor .dg-pill{display:none}}
</style>
<script>
(function(){if(window.dgPageInit)return;window.dgPageInit=true;
function init(){
 var rows=[].slice.call(document.querySelectorAll('.dg-score-row'));
 rows.forEach(function(r){r.setAttribute('role','button');r.setAttribute('tabindex','0');r.setAttribute('aria-expanded','false');
  function t(){var o=!r.classList.contains('is-open');r.classList.toggle('is-open',o);r.setAttribute('aria-expanded',String(o))}
  r.addEventListener('click',t);r.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' '){e.preventDefault();t()}})});
 var fs=[].slice.call(document.querySelectorAll('.dg-filter'));
 fs.forEach(function(f){f.addEventListener('click',function(){var v=f.getAttribute('data-filter');
  fs.forEach(function(b){b.setAttribute('aria-pressed',String(b===f))});
  rows.forEach(function(r){r.classList.toggle('is-hidden',v!=='all'&&!r.classList.contains('dg-status-'+v))})})});
 if(document.body.classList.contains('elementor-editor-active'))return;
 var bar=document.createElement('div');bar.className='dg-progress';bar.setAttribute('aria-hidden','true');document.body.appendChild(bar);
 var cta=document.createElement('div');cta.className='dg-sticky-cta';cta.innerHTML='<a href="#discovery">Request Complimentary Discovery</a>';document.body.appendChild(cta);
 var hero=document.querySelector('.dg-hero'),req=document.getElementById('discovery'),q=false;
 function u(){var m=document.documentElement.scrollHeight-innerHeight;bar.style.width=(m>0?scrollY/m*100:0)+'%';
  cta.classList.toggle('show',!!hero&&!!req&&hero.getBoundingClientRect().bottom<0&&req.getBoundingClientRect().top>innerHeight*.8);q=false}
 addEventListener('scroll',function(){if(!q){q=true;requestAnimationFrame(u)}},{passive:true});addEventListener('resize',u);u()}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init()})();
</script>"""


def status_line(color, label):
    return f'<span style="color:{HEX[color]};font-size:11px;margin-right:8px">&#9679;</span>{label}'


def hero():
    left = col([
        eyebrow(status_line("dggoldlt", "Digital infrastructure governance"), color="dggoldlt", _margin=dim(0, 0, 18, 0)),
        heading('Your portfolio has technology risk.<br><span style="color:#E0BC79">Get the full picture.</span>', "h1", "dgh1", "dgwhite"),
        text("Technology risk lives inside every portfolio company. We make it visible.", "dglead", "dgonhero", _margin=dim(26, 0, 0, 0)),
        text("For multi-location operators, every location gains the same clarity.", "dgintro", "dgondark", _margin=dim(14, 0, 0, 0)),
        text("TeleTech Solutions™ gives investors and operators an independent view of their systems, contracts, vendors and exposure.",
             "dgintro", "dgondark", _margin=dim(14, 0, 0, 0)),
        row([button(CTA, style="gold"), text_link("Explore the scorecard", "#scorecard", "dgonhero")], g=22, wrap="wrap",
            _title="Hero actions", margin=dim(29, 0, 0, 0)),
        text("One company. No cost. Clear options after discovery.", "dgxs", "dgondark", _margin=dim(14, 0, 0, 0)),
    ], width=sz(52, "%"), width_mobile=sz(100, "%"), _title="Hero copy")
    preview = card([
        col([eyebrow("Your executive view", _margin=dim(0, 0, 9, 0)),
             heading("Seven domains.<br>One clear scorecard.", "div", "dgh3", "dgink")], g=0),
        text("<p>" + "<br>".join([status_line("dggreen", "Good to go"), status_line("dgyellow", "Watch closely"),
                                   status_line("dgred", "Take action")]) + "</p>", "dgxs", "dgmuted"),
    ], pad=(24, 24), radius=12, flex_direction="row", flex_justify_content="space-between", flex_align_items="center",
        flex_gap=gap(20), html_tag="a", link=link("#scorecard"), margin=dim(-55, 22, 0, -22),
        margin_mobile=dim(-15, 12, 0, 12),
        box_shadow_box_shadow_type="yes",
        box_shadow_box_shadow={"horizontal": 0, "vertical": 15, "blur": 40, "spread": 0, "color": "rgba(0,0,0,0.2)"},
        _title="Scorecard preview card")
    right = col([image(IMG_HERO, "Business leaders reviewing a network operations overview", 16), preview],
                width=sz(48, "%"), width_mobile=sz(100, "%"), _title="Hero visual")
    top = row([left, right], g=55, padding=dim(70, 0, 54, 0), padding_mobile=dim(37, 0, 30, 0), _title="Hero grid")
    cred = grid([
        row([heading("1989", "div", "dgstat", "dggoldlt"), text("Building businesses since", "dgxs", "dgondark")], g=18, mobile_col=False),
        row([heading("07", "div", "dgstat", "dggoldlt"), text("Connected technology domains", "dgxs", "dgondark")], g=18, mobile_col=False),
        row([heading("01", "div", "dgstat", "dggoldlt"), text("Accountable advisor", "dgxs", "dgondark")], g=18, mobile_col=False),
    ], 3, 3, 1, g=30, padding=dim(25, 0, 32, 0), border_border="solid", border_width=dim(1, 0, 0, 0),
        border_color="rgba(255,255,255,0.15)", _title="Credibility")
    canvas = html(HERO_CANVAS, _position="absolute", _title="Hero motion (optional)")
    page_code = html(PAGE_CSS_JS, _position="absolute", _title="Page styles & scripts (keep)")
    return section([top, cred, canvas, page_code], pt=0, pb=0, pt_m=0, pb_m=0, css_classes="dg-hero", _title="Hero",
                   gradient={"background_background": "gradient", "background_color": "#071E30", "background_color_b": "#14374F",
                             "background_gradient_angle": sz(120, "deg")})


def audience():
    items = [
        ("Private Equity Firms", "Visibility into technology cost and exposure across every portfolio company."),
        ("Family Offices", "Single and multifamily offices protecting operating businesses and direct investments."),
        ("Investment Bankers, Capital Markets, and M&A Advisors",
         "Technology clarity your clients need before the raise, the letter of intent, or the sale. Fewer surprises in diligence."),
        ("Multi-Location Operators", "One governance layer across every location, vendor, and contract."),
    ]
    cards = [card([
        heading(f"{i + 1:02d}", "div", "dgintro", "dggolddk", _margin=dim(0, 0, 28, 0), _margin_mobile=dim(0, 0, 18, 0)),
        heading(t, "h3", "dgh3"),
        text(p, "dgsmall", _margin=dim(15, 0, 0, 0)),
    ], bg="dgcream", border="#E6E4DF", pad=(27, 23), pad_m=(22, 17), animation="fadeInUp") for i, (t, p) in enumerate(items)]
    return section([
        eyebrow("Who we serve", _margin=dim(0, 0, 18, 0)),
        heading("For the people<br>carrying the outcome.", "h2", "dgh2", _margin=dim(0, 0, 38, 0)),
        grid(cards, 4, 2, 1, g=18, _title="Audience cards"),
    ], bg="dgwhite", _title="Who we serve")


def problem():
    finds = ["Duplicate carriers and overlapping software contracts", "Auto-renewing agreements no one is reviewing",
             "Payment processing costs above market", "Security and PCI exposure without clear ownership",
             "Vendors managing themselves", "No documentation a buyer can rely on"]
    items = [card([
        heading(f"{i + 1:02d}", "div", "dgxs", "dggold", _margin=dim(3, 0, 0, 0)),
        text(f, "dgsmall", "dgwhite"),
    ], bg="dgnavy", border="rgba(255,255,255,0.13)", radius=9, pad=(21, 18), pad_m=(17, 14), flex_direction="row",
        flex_gap=gap(13), flex_align_items="flex-start") for i, f in enumerate(finds)]
    left = col([
        eyebrow("The hidden exposure", "dggold", _margin=dim(0, 0, 18, 0)),
        heading("What you cannot see is already on the balance sheet.", "h2", "dgh2", "dgwhite"),
        text("Most portfolio companies run technology that grew one decision at a time. No one owns the full picture.",
             "dgintro", "dgondark", _margin=dim(23, 0, 0, 0)),
        text("Every item affects EBITDA, risk, or valuation.", "dgemph", "dgwhite", _margin=dim(25, 0, 0, 0)),
    ], width=sz(47, "%"), width_mobile=sz(100, "%"))
    right = col([eyebrow("What we commonly find", "dggold", _margin=dim(0, 0, 18, 0)), grid(items, 2, 1, 1, g=16)],
                width=sz(53, "%"), width_mobile=sz(100, "%"))
    return section([row([left, right], g=80, align="flex-start", flex_gap_tablet=gap(40), flex_gap_mobile=gap(30))],
                   bg="dgnavy", anchor="approach", _title="The hidden exposure")


def lifecycle():
    top = row([
        col([eyebrow("Across the deal lifecycle", _margin=dim(0, 0, 18, 0)),
             heading("Governance at every<br>stage of ownership.", "h2", "dgh2")]),
        text("Select a stage to see where governance makes a difference.", "dgsmall",
             _element_width="initial", _element_custom_width=sz(360), _element_width_mobile="inherit"),
    ], g=50, align="flex-end", justify="space-between", flex_gap_mobile=gap(20), _title="Section top")
    t = tabs([
        ("Before Acquisition", "Technology diligence.", "Know the contracts, liabilities, and exposure you are about to own."),
        ("First 100 Days", "Consolidation.", "Remove duplicate services and bring vendors under one governance layer."),
        ("Hold Period", "Value creation.", "Cost recovery that flows to EBITDA and risk reduction across every location."),
        ("Before Exit or Capital Raise", "Readiness.",
         "Clean, documented infrastructure that supports valuation and survives buyer and investor diligence."),
    ])
    foot = row([text("One advisor. Every stage. Every location.", "dgemph", "dgink"), button(CTA)], g=25, justify="space-between",
               _title="Lifecycle footer")
    return section([top, t, foot], bg="dgcream", pt=80, pb=80, g=30, _title="Deal lifecycle")


def scorecard():
    def legend_item(color, title, body):
        label = status_line(color, '<strong style="color:#142E40">' + title + "</strong>")
        return text(f'<p>{label}<br>'
                    f'<span style="padding-left:19px;display:inline-block">{body}</span></p>', "dgxs", "dgmuted")
    legend = row([
        legend_item("dggreen", "Green · Good to Go", "Operating well. No immediate action."),
        legend_item("dgyellow", "Yellow · Watch Closely", "Monitor and plan ahead."),
        legend_item("dgred", "Red · Take Action", "High risk or cost leakage. Needs attention."),
    ], g=20, align="flex-start", wrap="wrap", mobile_col=False, margin=dim(22, 0, 0, 0), _title="Legend")
    top = row([
        col([eyebrow("What the Review delivers", _margin=dim(0, 0, 18, 0)),
             heading("See the business.<br>Know the priorities.", "h2", "dgh2")], width=sz(50, "%"), width_mobile=sz(100, "%")),
        col([
            text("The Digital Infrastructure Governance Review begins with Complimentary Discovery. Every Discovery ends with an "
                 "executive scorecard. Leadership sees where each domain stands and what deserves focus first.", "dgintro"),
            legend,
            text("Every rating is labeled Provisional, based on the information you share, or Verified, confirmed against your "
                 "invoices and agreements.", "dgxs", _margin=dim(14, 0, 0, 0)),
        ], width=sz(50, "%"), width_mobile=sz(100, "%")),
    ], g=65, align="flex-start", flex_gap_mobile=gap(20), _title="Scorecard intro")

    rows = [
        ("dgyellow", "Connectivity and WAN", "Watch Closely", "Two carriers at four locations. Contracts end on different dates."),
        ("dggreen", "Network Infrastructure", "Good to Go", "Current equipment supported. Documentation in place."),
        ("dgyellow", "Communications and AI", "Watch Closely", "Phone system renewal in 90 days. No competitive review completed."),
        ("dgred", "Security and Compliance", "Take Action", "PCI responsibility not assigned. No incident response plan."),
        ("dgyellow", "Cloud, Data and Resiliency", "Watch Closely", "Backups run. Recovery never tested."),
        ("dgred", "POS and Merchant Services", "Take Action", "Processing rate above market. Contract terms not on file."),
        ("dggreen", "Business Operations Technology", "Good to Go", "Help desk responsive. Vendor list current."),
    ]
    head = row([
        col([eyebrow("Sample Scorecard", "dggold"), heading("Executive overview", "h3", "dgh3", "dgwhite", _margin=dim(5, 0, 0, 0))]),
        heading("7", "div", "dgstat", "dggold"),
    ], g=20, justify="space-between", mobile_col=False, padding=dim(25, 30), padding_mobile=dim(22),
        background_background="classic", __globals__={"background_color": C("dgnavy")}, _title="Scorecard head")
    filters = html('<div class="dg-filters" aria-label="Filter sample findings">'
                   '<button type="button" class="dg-filter" data-filter="all" aria-pressed="true">All domains</button>'
                   '<button type="button" class="dg-filter" data-filter="red" aria-pressed="false">Take action</button>'
                   '<button type="button" class="dg-filter" data-filter="yellow" aria-pressed="false">Watch closely</button>'
                   '<button type="button" class="dg-filter" data-filter="green" aria-pressed="false">Good to go</button></div>',
                   _title="Scorecard filters")
    body = [filters]
    for color, name, label, finding in rows:
        status = color.replace("dg", "")
        body.append(row([
            dot(color, 9),
            col([heading(name, "h4", "dgemph", "dgink", __globals__={"typography_typography": T("dgintro"), "title_color": C("dgink")}),
                 text(finding, "dgsmall", _css_classes="dg-finding")], g=6, _flex_size="grow"),
            heading(label, "div", "dgxs", "dgmuted", _padding=dim(4, 12), _border_border="solid", _border_width=dim(1),
                    _border_color=HEX["dgline"], _border_radius=dim(20), _css_classes="dg-pill"),
        ], g=15, mobile_col=False, padding=dim(20, 30), padding_mobile=dim(18), border_border="solid",
            border_width=dim(0, 0, 1, 0), border_color=HEX["dgline"], background_background="classic",
            background_color="#FFFFFF", css_classes=f"dg-score-row dg-status-{status}", _title=name))
    note = con([text("Select a domain to see its finding. Illustrative examples only, not an assessment of your business.", "dgxs")],
               padding=dim(18, 30), padding_mobile=dim(17), background_background="classic",
               __globals__={"background_color": C("dgpaper")})
    sc = card([head] + body + [note], radius=13, pad=(0, 0), overflow="hidden", box_shadow_box_shadow_type="yes",
              box_shadow_box_shadow={"horizontal": 0, "vertical": 12, "blur": 35, "spread": 0, "color": "rgba(20,46,64,0.05)"},
              _title="Scorecard")
    return section([top, sc], bg="dgwhite", g=35, anchor="scorecard", _title="Scorecard")


def domains():
    callout = con([
        heading("7 connected domains", "div", "dgh3", "dgink"),
        text("Systems, suppliers and contracts work together. Your governance should too.", "dgsmall", _margin=dim(10, 0, 18, 0)),
        text_link("Discuss your infrastructure", "#discovery"),
    ], flex_direction="column", flex_align_items="flex-start", padding=dim(25), margin=dim(30, 0, 0, 0),
        border_border="solid", border_width=dim(0, 0, 0, 3), border_radius=dim(0, 9, 9, 0),
        background_background="classic", __globals__={"background_color": C("dgstone"), "border_color": C("dggold")},
        _title="Callout")
    left = col([
        eyebrow("What we govern", _margin=dim(0, 0, 18, 0)),
        heading("The entire infrastructure.<br>Under one view.", "h2", "dgh2"),
        text("Explore the seven domains behind a complete technology picture.", "dgintro", _margin=dim(23, 0, 0, 0)),
        callout,
    ], width=sz(40, "%"), width_mobile=sz(100, "%"))
    acc = accordion([
        ("Connectivity and WAN", "Internet, failover, SD-WAN, mobile and IoT"),
        ("Network Infrastructure", "Wi-Fi, LAN, firewall, switching, cabling"),
        ("Communications and AI", "Voice, contact center, AI ordering, customer engagement"),
        ("Security and Compliance", "Cybersecurity, PCI, identity and access, regulatory compliance"),
        ("Cloud, Data and Resiliency", "Cloud, backup, disaster recovery, reporting"),
        ("POS and Merchant Services", "POS platform, processing economics, gift and loyalty, integrations"),
        ("Business Operations Technology", "Applications, help desk, lifecycle planning, vendor governance, cost optimization"),
    ], numbered=True)
    right = col([acc, text("These seven domains form the foundation of every review. Industry frameworks for QSR, franchise and "
                           "multi-location operators extend them into detailed governance lanes.", "dgsmall", _margin=dim(20, 0, 0, 0))],
                width=sz(60, "%"), width_mobile=sz(100, "%"))
    return section([row([left, right], g=70, align="flex-start", flex_gap_tablet=gap(40), flex_gap_mobile=gap(30))],
                   bg="dgcream", _title="Seven domains")


def process():
    def step(n, title, outcome, body):
        circle = con([heading(f"{n:02d}", "div", "dgxs", "dggolddk")], width=sz(34), min_height=sz(34),
                     flex_direction="column", flex_justify_content="center", flex_align_items="center",
                     border_radius=dim(50, unit="%"), background_background="classic",
                     __globals__={"background_color": C("dgcream")}, _flex_size="none")
        return row([circle, col([
            heading(title, "h3", "dgh3", _margin=dim(5, 0, 0, 0)),
            text(f'<p><strong style="color:#9A7132">{outcome}</strong> {body}</p>', "dgsmall"),
        ], g=8)], g=12, align="flex-start", mobile_col=False, padding=dim(0, 0, 23, 0), border_border="solid",
            border_width=dim(0, 0, 1, 0), border_color=HEX["dgline"], _title=f"Step {n}")
    steps = col([
        step(1, "Discovery Conversation", "Measured.", "Your locations, providers, costs and contracts, documented. Thirty minutes."),
        step(2, "Observation", "Reviewed.", "Seven domains evaluated and rated."),
        step(3, "Findings Meeting", "Planned.", "Your scorecard and clear options, delivered."),
        text("No obligation. No pressure. Clarity first.", "dgemph", "dgink"),
    ], g=22, width=sz(50, "%"), width_mobile=sz(100, "%"))
    photo = col([image(IMG_PROCESS, "Reggie Hilliard meeting with business leaders", 14,
                       caption="Clarity begins with a conversation.", shadow=False)], width=sz(50, "%"), width_mobile=sz(100, "%"))
    return section([
        eyebrow("How it works", _margin=dim(0, 0, 18, 0)),
        heading("Measured. Reviewed. Planned.", "h2", "dgh2", _margin=dim(0, 0, 12, 0)),
        text("We do not replace providers that are working. We govern your infrastructure through its full lifecycle.",
             "dgintro", _element_width="initial", _element_custom_width=sz(760), _element_width_mobile="inherit",
             _margin=dim(0, 0, 25, 0)),
        row([photo, steps], g=65, align="flex-start", flex_gap_tablet=gap(35), flex_gap_mobile=gap(27), animation="fadeInUp"),
    ], bg="dgwhite", _title="How it works")


def founder():
    principles = grid([
        col([eyebrow("The role", "dggold", _margin=dim(0, 0, 8, 0)), text("Architect, not implementer.", "dgemph", "dgwhite")]),
        col([eyebrow("The relationship", "dggold", _margin=dim(0, 0, 8, 0)), text("Trusted Advisor, not vendor.", "dgemph", "dgwhite")]),
    ], 2, 2, 2, g=25, margin=dim(28, 0, 0, 0), padding=dim(23, 0, 0, 0), border_border="solid",
        border_width=dim(1, 0, 0, 0), border_color="rgba(255,255,255,0.15)", _title="Principles")
    copy_ = col([
        eyebrow("Meet your advisor", "dggold", _margin=dim(0, 0, 18, 0)),
        heading("Experience you can<br>put a name to.", "h2", "dgh2", "dgwhite"),
        text("<p>Reggie Hilliard founded TeleTech Solutions™ to give leadership one trusted advisor across the full technology "
             "picture. He has operated in business since 1989 and began his career as an underwriter, evaluating risk before it "
             "became loss.</p><p>Reggie has been building businesses since 1989.</p>", "dgintro", "dgondark", spacing=23,
             _margin=dim(23, 0, 0, 0)),
        heading("Reggie Hilliard", "div", "dgh3lg", "dgwhite", _margin=dim(0, 0, 4, 0)),
        text("Founder, TeleTech Solutions™", "dgsmall", "dgondark"),
        principles,
    ], width=sz(55, "%"), width_mobile=sz(100, "%"))
    photo = col([image(IMG_FOUNDER, "Reggie Hilliard, founder of TeleTech Solutions", 14, shadow=False)],
                width=sz(45, "%"), width_mobile=sz(100, "%"), _title="Founder photo")
    return section([row([photo, copy_], g=80, flex_gap_tablet=gap(40), flex_gap_mobile=gap(30), animation="fadeInUp")],
                   bg="dgdeep", pt=80, pb=80, _title="Founder")


def engagement():
    tiers = [
        ("Complimentary Discovery", "No charge", "Initial clarity", True,
         ["Initial conversation and observation", "High-level opportunity identification",
          "Executive scorecard showing what needs attention and where to focus first"]),
        ("Executive Advisory", "$300", "per hour", False,
         ["Focused leadership guidance", "Supplier review and contract discussion", "Strategic decision support",
          "Recommended 4-hour minimum"]),
        ("Governance Blueprint", "$7,500", "starting at", False,
         ["Detailed structured review", "Current, Aging, and Future-Ready lifecycle classification",
          "Good, Better, Best recommendations", "90-day executive roadmap"]),
        ("Infrastructure Governance", "$5,000–$10,000+", "per month", False,
         ["Supplier governance", "Lifecycle management and recurring review", "Executive guidance", "Infrastructure accountability"]),
    ]
    cards = []
    for i, (title, price, meta, featured, bullets) in enumerate(tiers):
        lst = widget("icon-list", icon_list=[{"text": b, "selected_icon": icon("fas fa-circle"), "_id": uid()} for b in bullets],
                     _css_classes="dg-tier-list", icon_size=sz(5), text_indent=sz(10), icon_self_vertical_align="flex-start",
                     icon_vertical_offset=sz(9),
                     __globals__={"icon_typography_typography": T("dgsmall"), "icon_color": C("dggolddk"), "text_color": C("dgmuted")})
        cards.append(card([
            eyebrow(f"{i + 1:02d} / Engagement", _margin=dim(0, 0, 18, 0)),
            row([heading(title, "h3", "dgh3"),
                 col([heading(price, "div", "dgprice", "dgink", align="right"),
                      text(meta, "dgxs", align="right", _margin=dim(5, 0, 0, 0))], width=sz(45, "%"))],
                g=20, align="flex-start", justify="space-between", mobile_col=False),
            con([lst], margin=dim(22, 0, 0, 0), padding=dim(20, 0, 0, 0), border_border="solid",
                border_width=dim(1, 0, 0, 0), border_color="#D5D9D7"),
        ], bg="dgsand" if featured else "dgwhite", border="#CCBB9A" if featured else "dgline", pad=(28, 30), pad_m=(25, 25),
            _title=title))
    distinction = grid([
        col([heading("Discovery scorecard", "h3", "dgh3"), text("What needs attention and where to focus.", "dgsmall")], g=7),
        col([heading("Governance Blueprint", "h3", "dgh3"),
             text("Why it matters, what it costs, what to do, and in what order.", "dgsmall")], g=7),
    ], 2, 2, 1, g=40, padding=dim(25, 0), border_border="solid", border_width=dim(1, 0, 0, 0), border_color=HEX["dgline"])
    return section([
        eyebrow("Engagement options", _margin=dim(0, 0, 18, 0)),
        heading("Start small. Build with clarity.", "h2", "dgh2", _margin=dim(0, 0, 35, 0)),
        grid(cards, 2, 2, 1, g=18, animation="fadeInUp", _title="Pricing"),
        text("Portfolio engagements priced by number of companies and locations.", "dgxs", _margin=dim(22, 0, 0, 0)),
        text("Some compensation may come through supplier channel relationships.", "dgxs", _margin=dim(6, 0, 22, 0)),
        distinction,
        row([text("Start with one company. See the full picture.", "dgemph", "dgink"), button(CTA)], g=30,
            justify="space-between", margin=dim(16, 0, 0, 0)),
    ], bg="dgcream", anchor="engagement", _title="Engagement options")


def faq():
    left = col([eyebrow("Before we talk", _margin=dim(0, 0, 18, 0)), heading("A few things<br>you may be wondering.", "h2", "dgh2")],
               width=sz(45, "%"), width_mobile=sz(100, "%"))
    acc = accordion([
        ("What happens in the first conversation?",
         "A 30-minute discovery conversation focuses on your business, locations and priorities. We establish a starting point for the Review."),
        ("What will leadership receive?",
         "An executive scorecard across seven technology domains, followed by a findings conversation and clear options for what happens next."),
        ("Is discovery a paid engagement?",
         "Discovery is complimentary. Detailed analysis, architecture and ongoing governance are professional services, outlined in the engagement options above."),
        ("Can we begin with one company?",
         "Yes. Start with one portfolio company at no cost, see the scorecard, then decide whether governance should extend across your portfolio."),
        ("How is our information protected?",
         "A mutual confidentiality agreement is signed before any documents are shared. Documents move through a secure, private folder, never by email with full account numbers."),
    ], content_pad=(0, 0, 23, 0))
    right = col([acc], width=sz(55, "%"), width_mobile=sz(100, "%"))
    return section([row([left, right], g=80, align="flex-start", flex_gap_tablet=gap(40), flex_gap_mobile=gap(30))],
                   bg="dgwhite", _title="FAQ")


def pro_form():
    fields = [
        ("full_name", "text", "Full name", "50"), ("firm_name", "text", "Firm name", "50"),
        ("role", "text", "Role", "50"), ("companies_or_locations", "text", "Number of portfolio companies or locations", "50"),
        ("email", "email", "Email", "50"), ("phone", "tel", "Phone", "50"),
    ]
    return widget(
        "form",
        form_name="Discovery",
        form_fields=[{"custom_id": cid, "field_type": ft, "field_label": lbl, "placeholder": "", "required": "true",
                      "width": w, "width_mobile": "100", "_id": uid()} for cid, ft, lbl, w in fields]
                    # privacy note sits between the fields and the button, as in the original page
                    + [{"custom_id": "privacy_note", "field_type": "html", "field_label": "", "field_html": PRIVACY_NOTE,
                        "width": "100", "_id": uid()}],
        _css_classes="dg-form",
        input_size="sm",
        show_labels="yes",
        mark_required="",
        button_text=CTA,
        button_size="md",
        button_width="100",
        submit_actions=["email"],
        email_to="Reggie@TeleTechTX.com",
        email_subject="New Complimentary Discovery request — TheDIGF.com",
        email_content="[all-fields]",
        email_from_name="TheDIGF.com",
        success_message="Thank you. Reggie will contact you within two business days to schedule your discovery conversation.",
        error_message="Your request could not be sent. Please email Reggie@TeleTechTX.com or call 945.262.8477.",
        required_field_message="This field is required.",
        invalid_message="There's something wrong. The form is invalid.",
        column_gap=sz(18),
        row_gap=sz(14),
        label_spacing=sz(6),
        field_border_radius=dim(6),
        field_background_color="#FAFBFA",
        field_border_color="#D3DCDC",
        button_border_radius=dim(7),
        __globals__={
            "label_color": C("dgink"), "label_typography_typography": T("dgemph"),
            "field_text_color": C("dgink"), "field_typography_typography": T("dgsmall"),
            "button_background_color": C("dgnavy"), "button_text_color": C("dgwhite"),
            "button_background_hover_color": C("dgnavyhv"), "button_hover_color": C("dgwhite"),
            "button_typography_typography": T("dgbutton"),
        },
    )


def free_form():
    return widget("shortcode", shortcode='[contact-form-7 id="REPLACE-WITH-YOUR-FORM-ID" title="Discovery"]',
                  _title="Form (replace shortcode with your form plugin)")


def request(pro=True):
    def benefit(n, label):
        return row([heading(f"{n:02d}", "div", "dgxs", "dggolddk", _flex_size="none", _element_width="initial",
                            _element_custom_width=sz(24)), text(label, "dgsmall", "dgink")],
                   g=12, align="flex-start", mobile_col=False)
    contact = col([
        heading("A direct conversation with Reggie.", "div", "dgemph", "dgink"),
        heading("945.262.8477", "div", "dgsmall", "dgink", url="tel:+19452628477", _margin=dim(9, 0, 0, 0)),
        heading("Reggie@TeleTechTX.com", "div", "dgsmall", "dgink", url="mailto:Reggie@TeleTechTX.com", _margin=dim(4, 0, 0, 0)),
    ], padding=dim(20, 0, 0, 0), margin=dim(24, 0, 0, 0), border_border="solid", border_width=dim(1, 0, 0, 0),
        border_color="#C8C0B2", _title="Contact")
    left = col([
        eyebrow("Request Complimentary Discovery", _margin=dim(0, 0, 18, 0)),
        heading("Let’s make your<br>next decision clearer.", "h2", "dgh2", _css_classes="dg-request-title"),
        text("We review one company at no cost. You see the scorecard. You decide whether governance should extend across the portfolio.",
             "dgintro", _margin=dim(20, 0, 0, 0)),
        col([benefit(1, "A 30-minute discovery conversation"), benefit(2, "One company reviewed at no cost"),
             benefit(3, "A clear scorecard and options for your next step")], g=11, margin=dim(23, 0, 0, 0)),
        contact,
    ], width=sz(34, "%"), width_mobile=sz(100, "%"))
    form_card = card([
        heading("Request your discovery conversation", "h3", "dgh3"),
        text("Tell us about your firm. Reggie will coordinate the next step with you.", "dgsmall", _margin=dim(9, 0, 20, 0)),
    ] + ([pro_form()] if pro else [free_form(), text(PRIVACY_NOTE, "dgxs", _margin=dim(14, 0, 0, 0))]), border="#D8D2C6", radius=14, pad=(28, 28), pad_m=(23, 20), width=sz(66, "%"), width_mobile=sz(100, "%"),
        box_shadow_box_shadow_type="yes",
        box_shadow_box_shadow={"horizontal": 0, "vertical": 12, "blur": 35, "spread": 0, "color": "rgba(20,46,64,0.05)"},
        _title="Form card")
    return section([row([left, form_card], g=48, align="stretch", flex_gap_tablet=gap(28), flex_gap_mobile=gap(30))],
                   bg="dgsand", anchor="discovery", _title="Request discovery")


# ---------------------------------------------------------------- page: PRIVACY
def privacy_page():
    paras = [
        "TeleTech Solutions, LLC collects the contact information you submit through this site to schedule and conduct your discovery conversation.",
        "We do not sell, rent or share your information with third parties for marketing.",
        "Documents shared during an engagement are protected by a mutual confidentiality agreement and stored in access-controlled folders.",
        'To update or remove your information, email <a href="mailto:Reggie@TeleTechTX.com">Reggie@TeleTechTX.com</a>.',
        "TeleTech Solutions, LLC · 5 Cowboys Way, Suite 300, Frisco, Texas 75034",
    ]
    article = card([
        heading("Privacy", "h1", "dgh2", _margin=dim(0, 0, 28, 0)),
        text("".join(f"<p>{p}</p>" for p in paras[:-1]), "dgintro", spacing=18),
        text(paras[-1], "dgintro"),
    ], radius=12, pad=(40, 40), pad_m=(26, 22), _title="Privacy article")
    return [section([article], bg="dgcream", pt=72, pb=72, pt_m=34, pb_m=34, boxed=820, _title="Privacy")]


# ---------------------------------------------------------------- finalise
def mark_inner(elements, depth=0):
    for e in elements:
        if e["elType"] == "container":
            e["isInner"] = depth > 0
        mark_inner(e["elements"], depth + 1)
    return elements


# "Elementor Full Width": keeps the theme / Theme Builder header and footer, hides the page title.
PAGE_SETTINGS = {"template": "elementor_header_footer", "hide_title": "yes"}


def template(title, content, page_settings=None):
    return {"content": mark_inner(content), "page_settings": dict(page_settings or PAGE_SETTINGS), "version": "0.4",
            "title": title, "type": "page"}


def home(pro=True):
    # Header and footer come from the theme / Elementor Theme Builder, so the page holds content sections only.
    return [hero(), audience(), problem(), lifecycle(), scorecard(), domains(), process(), founder(), engagement(),
            faq(), request(pro)]


def main():
    os.makedirs(OUT, exist_ok=True)
    # deterministic ids per file
    random.seed(1)
    home_pro = template("TheDIGF – Home", home(True))
    random.seed(2)
    home_free = template("TheDIGF – Home (no Elementor Pro)", home(False))
    random.seed(3)
    privacy = template("TheDIGF – Privacy", privacy_page())
    files = {
        "thedigf-home.json": home_pro,
        "thedigf-home-free-form.json": home_free,
        "thedigf-privacy.json": privacy,
    }
    for name, data in files.items():
        with open(os.path.join(OUT, name), "w", encoding="utf-8") as f:
            json.dump(data, f, ensure_ascii=False, indent=1)

    # global style kit (Elementor > Tools > Import / Export Kit)
    manifest = {
        "name": "thedigf-global-style-kit",
        "title": "TheDIGF Global Style Kit",
        "description": "Global colours, global fonts, theme styles, buttons, form fields and layout for TheDIGF.com.",
        "author": "TeleTech Solutions",
        "version": "2.0",
        "elementor_version": "3.30.0",
        "created": "2026-10-05 00:00:00",
        "thumbnail": False,
        "site": "https://thedigf.com",
        "site-settings": ["global-colors", "global-typography", "theme-style-typography", "theme-style-buttons",
                          "theme-style-images", "theme-style-form-fields", "settings-layout", "settings-background"],
    }
    site_settings = {"content": [], "settings": kit_settings(), "metadata": []}
    kit = os.path.join(OUT, "thedigf-global-style-kit.zip")
    with zipfile.ZipFile(kit, "w", zipfile.ZIP_DEFLATED) as z:
        z.writestr("manifest.json", json.dumps(manifest, ensure_ascii=False, indent=1))
        z.writestr("site-settings.json", json.dumps(site_settings, ensure_ascii=False, indent=1))
    print("written to", os.path.abspath(OUT))


if __name__ == "__main__":
    main()
