<?php

namespace Tainacan;

class Dashboard extends Pages {
	use \Tainacan\Traits\Singleton_Instance;

	protected function get_page_slug() : string {
        return 'tainacan_dashboard';
    }
	private $vue_component_page_slug = 'tainacan_admin';
	private $tainacan_dashboard_cards = [];
	private $disabled_cards = [];

	function add_admin_menu() {
		// Main Page, Dashboard
		$dashboard_page_suffix = add_menu_page(
			__( 'Tainacan', 'tainacan' ),
			__( 'Tainacan', 'tainacan' ),
			'read',
			$this->get_page_slug(),
			array( &$this, 'render_page' ),
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMi4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4KCjxzdmcKICAgdmVyc2lvbj0iMS4xIgogICBpZD0iQ2FtYWRhXzEiCiAgIHg9IjBweCIKICAgeT0iMHB4IgogICB2aWV3Qm94PSIwIDAgMjQuMDAwMDAyIDI0LjAwNTAzNyIKICAgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIKICAgd2lkdGg9IjI0LjAwMDAwMiIKICAgaGVpZ2h0PSIyNC4wMDUwMzciCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnMKICAgaWQ9ImRlZnMxMSI+CgoJCgkKCgoJCQoJCQoJCQoJCQoJCQoJCQoJCQoJCQoJCgkJCQoJCQkKCQk8L2RlZnM+CjxzdHlsZQogICB0eXBlPSJ0ZXh0L2NzcyIKICAgaWQ9InN0eWxlMSI+Cgkuc3Qwe2ZpbGw6I0ZGRkZGRjt9Cjwvc3R5bGU+CjxwYXRoCiAgIGNsYXNzPSJzdDAiCiAgIGQ9Ik0gMjMuNDM5NDE2LDE2LjMzNzQ0MyBDIDIyLjc2NjM5OCwxNC45OTE0MDcgMjEuNTQwNTQ0LDEzLjc1MzUzNCAxOS43ODU4OSwxMi42ODM5MTcgbCAwLjAxMjAyLC0wLjAxMjAyIHYgMCBDIDIxLjE5MjAxNywxMS4wMTMzOSAyMS45OTcyMzUsOS4zOTA5MzU4IDIyLjIwMTU0NCw3Ljg0MDU5MSAyMi40Mjk4ODksNi4xNDYwMjgxIDIxLjkwMTA4OSw0LjYwNzcwMTQgMjAuNjg3MjUzLDMuMzgxODQ3MyBsIC0wLjAxMjAyLC0wLjAxMjAxOCBDIDE5LjQ2MTM5NywyLjE1NTk5MzQgMTcuOTExMDUzLDEuNjI3MTkzNiAxNi4yMTY0OSwxLjg1NTUzOSAxNC42NjYxNDUsMi4wNTk4NDggMTMuMDMxNjczLDIuODc3MDg0IDExLjM3MzE2NCw0LjI3MTE5MjUgdiAtMC4wMTIwMTggMCBDIDEwLjMwMzU0OSwyLjQ5MjUwMjIgOS4wNzc2OTQ3LDEuMjU0NjMgNy43MTk2NDA3LDAuNTY5NTkzOTQgNi4yMjkzODY3LC0wLjE3NTUzMzAzIDQuNjQyOTg3NCwtMC4xODc1NTEyMSAzLjExNjY3ODksMC41MjE1MjEyMyBsIC0wLjAyNDAzNiwwLjAxMjAxODIgQyAtMC4zMDg1MDE2MSwyLjEzMTk1NjkgLTAuOTQ1NDY0OTksNi4wNjE5MDA4IDEuMzg2MDYxNCwxMS4wNjE0NjIgbCAwLjA0ODA3MywwLjEwODE2NCBjIDAuMzk2NTk5OSwxLjA0NTU4MiAxLjE1Mzc0NSwyLjY1NjAxOCAyLjUzNTgzNTQsNC40NDY3MjYgdiAwIDAgYyAtMC41NjQ4NTQ0LDIuMDA3MDM1IC0wLjY3MzAxOCwzLjY0MTUwOCAtMC42NzMwMTgsNC42NTEwMzQgdiAwLjQ4MDcyNyBoIDAuNDgwNzI3MSBjIDEuMDA5NTI2OSwwIDIuNjQzOTk5LC0wLjEyMDE4MSA0LjY2MzA1MjcsLTAuNjg1MDM2IHYgMCAwIGMgMS43Nzg2ODk0LDEuMzU4MDU0IDMuMzc3MTA3NCwyLjExNTIgNC4zOTg2NTI0LDIuNDk5NzgxIGwgMC4xMjAxODIsMC4wNjAwOSBjIDEuOTcwOTgxLDAuOTEzMzgyIDMuNzczNzA3LDEuMzgyMDkgNS4zNzIxMjUsMS4zODIwOSAwLjM5NjYsMCAwLjc4MTE4MSwtMC4wMjQwNCAxLjE0MTcyNywtMC4wODQxMyAxLjgzODc4MSwtMC4zMDA0NTQgMy4yMjA4NzEsLTEuMzM0MDE3IDQuMDAyMDUyLC0zLjAwNDU0NCBsIDAuMDEyMDIsLTAuMDI0MDQgQyAyNC4xODQ1NDQsMTkuNDAyMDcgMjQuMTcyNTI2LDE3LjgxNTY3IDIzLjQzOTQyLDE2LjMzNzQzNSBaIE0gMTEuNjI1NTQ4LDYuOTAzMTczMiBjIDAuMDEyMDIsMC4wMzYwNTQgMC4wMjQwNCwwLjA3MjEwOSAwLjA0ODA3LDAuMDk2MTQ1IGwgMC4wMjQwNCwwLjA2MDA5MSBjIDAuMDQ4MDcsMC4xMDgxNjM2IDAuMDk2MTUsMC4yMjgzNDU0IDAuMTQ0MjE4LDAuMzM2NTA5IGwgMC4wODQxMywwLjE5MjI5MDggYyAwLjA0ODA3LDAuMTA4MTYzNiAwLjA4NDEzLDAuMjE2MzI3MiAwLjEzMjE5OSwwLjMxMjQ3MjYgMC4wMjQwNCwwLjA2MDA5MSAwLjA0ODA3LDAuMTMyMTk5OSAwLjA3MjExLDAuMTkyMjkwOCAwLjAzNjA1LDAuMTA4MTYzNiAwLjA4NDEzLDAuMjE2MzI3MiAwLjEyMDE4MiwwLjMxMjQ3MjYgbCAwLjA3MjExLDAuMTkyMjkwOSBjIDAuMDM2MDUsMC4xMDgxNjM2IDAuMDg0MTMsMC4yMjgzNDUzIDAuMTIwMTgyLDAuMzM2NTA4OSBsIDAuMDYwMDksMC4xNjgyNTQ1IGMgMC4wNDgwNywwLjE0NDIxODEgMC4wOTYxNCwwLjI4ODQzNjIgMC4xNDQyMTgsMC40MzI2NTQ0IGwgMC4wMjQwNCwwLjA3MjEwOSBjIDAuMDI0MDQsMC4wOTYxNDUgMC4wNjAwOSwwLjE5MjI5MDkgMC4wODQxMywwLjI4ODQzNjMgQyAxMi42MTEwNDgsOS44NTk2NDQgMTIuNDY2ODMsOS44MzU2MDggMTIuMzIyNjEyLDkuNzk5NTU0IEwgMTIuMjYyNTIyLDkuNzg3NTM2IEMgMTIuMDgyMjM5LDkuNzUxNDgxIDExLjkxMzk4NCw5LjcxNTQyNyAxMS43MzM3MTIsOS42OTEzOSBMIDExLjYxMzUzLDkuNjY3MzUzOSBDIDExLjQzMzI1Nyw5LjYzMTI5OTQgMTEuMjQwOTY3LDkuNjA3MjYzIDExLjA2MDY5NCw5LjU3MTIwODUgTCAxMC45NDA1MTIsOS41NTkxOTAzIEMgMTAuNzcyMjU4LDkuNTM1MTU0IDEwLjYwNDAwMyw5LjUxMTExNzYgMTAuNDM1NzQ5LDkuNDg3MDgxMyBsIC0wLjA4NDEzLC0wLjAxMjAxOCBDIDEwLjE3MTM0OSw5LjQ1MTAyNjcgOS45NzkwNTgsOS40MzkwMDg2IDkuNzk4Nzg1Myw5LjQxNDk3MjIgTCA5LjY2NjU4NTQsOS40MDI5NTQgQyA5LjQ3NDI5NDUsOS4zOTA5MzU4IDkuMjk0MDIxOSw5LjM2Njg5OTUgOS4xMDE3MzEsOS4zNTQ4ODEzIEggOS4wNjU2NzY1IEMgOC44ODU0MDM4LDkuMzQyODYzMSA4LjcwNTEzMTIsOS4zMzA4NDUgOC41MzY4NzY3LDkuMzMwODQ1IEggOC40MTY2OTQ5IGMgLTAuMTIwMTgxNywwIC0wLjI0MDM2MzUsMCAtMC4zNjA1NDUzLC0wLjAxMjAxOCAwLjA3MjEwOSwtMC4wOTYxNDUgMC4xMzIyLC0wLjE5MjI5MDggMC4yMDQzMDksLTAuMjg4NDM2MyAwLjAxMjAxOCwtMC4wMjQwMzYgMC4wMzYwNTUsLTAuMDQ4MDczIDAuMDQ4MDczLC0wLjA3MjEwOSBsIDAuMDM2MDU1LC0wLjA0ODA3MyBDIDguNDQwNzMxMyw4Ljc5MDAyNyA4LjUyNDg1ODUsOC42Njk4NDUyIDguNjIxMDA0LDguNTQ5NjYzNSA4LjY1NzA1OSw4LjUxMzYwODUgOC42ODEwOTUsOC40Nzc1NTQ1IDguNzE3MTQ5LDguNDI5NDgxNyBMIDguNzY1MjIyLDguMzY5MzkwNyBDIDguODQ5MzQ5Myw4LjI2MTIyNzIgOC45MjE0NTg0LDguMTY1MDgxOCA5LjAwNTU4NTYsOC4wNTY5MTgyIDkuMDUzNjU4Myw3Ljk5NjgyNzMgOS4xMDE3MzEsNy45MzY3MzY0IDkuMTYxODIxOSw3Ljg2NDYyNzQgOS4yNDU5NDkyLDcuNzY4NDgyIDkuMzMwMDc2NCw3LjY3MjMzNjUgOS40MDIxODU1LDcuNTc2MTkxMSA5LjQ1MDI1ODIsNy41MTYxMDAyIDkuNTEwMzQ5MSw3LjQ1NjAwOTMgOS41NzA0Mzk5LDcuMzgzOTAwMyA5LjY1NDU2NzIsNy4yODc3NTQ5IDkuNzM4Njk0NCw3LjE5MTYwOTUgOS44MjI4MjE3LDcuMDk1NDY0IGwgMC4wMjQwMzYsLTAuMDI0MDM2IGMgMC4wNDgwNzMsLTAuMDQ4MDczIDAuMDg0MTI3LC0wLjA5NjE0NSAwLjEzMjIsLTAuMTU2MjM2MyAwLjA5NjE0NSwtMC4xMDgxNjM2IDAuMTkyMjkxMywtMC4yMDQzMDkgMC4yODg0MzYzLC0wLjMxMjQ3MjYgbCAwLjA0ODA3LC0wLjA2MDA5MSBjIDAuMDI0MDQsLTAuMDM2MDU1IDAuMDYwMDksLTAuMDYwMDkxIDAuMDg0MTMsLTAuMDk2MTQ1IDAuMTQ0MjE4LC0wLjE1NjIzNjMgMC4yODg0MzYsLTAuMzAwNDU0NCAwLjQ0NDY3MywtMC40NTY2OTA3IDAuMDg0MTMsLTAuMDg0MTI3IDAuMTU2MjM2LC0wLjE1NjIzNjMgMC4yNDAzNjMsLTAuMjQwMzYzNiAwLjAyNDA0LDAuMDQ4MDczIDAuMDQ4MDcsMC4xMDgxNjM2IDAuMDg0MTMsMC4xNTYyMzYzIGwgMC4wNDgwNywwLjA5NjE0NSBjIDAuMDYwMDksMC4xMDgxNjM2IDAuMTA4MTY0LDAuMjI4MzQ1NCAwLjE2ODI1NSwwLjMzNjUwOSBsIDAuMTA4MTYzLC0wLjA0ODA3MyAtMC4wOTYxNCwwLjA2MDA5MSBjIDAuMDg0MTMsMC4yMjgzNDUzIDAuMTU2MjM2LDAuMzk2NTk5OCAwLjIyODM0NSwwLjU1MjgzNjEgeiBtIDguMzc2NjY5LC0yLjgyNDI3MTYgYyAyLjAxOTA1NCwyLjAzMTA3MTkgMS42MjI0NTQsNC45ODc1NDM1IC0xLjA2OTYxNyw4LjEzNjMwNjQgLTAuMTIwMTgyLC0wLjA2MDA5IC0wLjI0MDM2NCwtMC4xMjAxODIgLTAuMzQ4NTI4LC0wLjE4MDI3MyBsIC0wLjA5NjE0LC0wLjA0ODA3IGMgLTAuMTQ0MjE4LC0wLjA3MjExIC0wLjMwMDQ1NCwtMC4xNDQyMTggLTAuNDQ0NjczLC0wLjIxNjMyNyAtMC4xODAyNzIsLTAuMDg0MTMgLTAuMzYwNTQ1LC0wLjE2ODI1NSAtMC41NDA4MTcsLTAuMjQwMzY0IC0wLjAzNjA2LC0wLjAxMjAyIC0wLjA3MjExLC0wLjAyNDA0IC0wLjA5NjE1LC0wLjA0ODA3IGwgLTAuMDg0MTMsLTAuMDM2MDUgYyAtMC4xMjAxODIsLTAuMDQ4MDcgLTAuMjQwMzY0LC0wLjEwODE2MyAtMC4zNjA1NDUsLTAuMTU2MjM2IC0wLjA0ODA3LC0wLjAyNDA0IC0wLjA4NDEzLC0wLjAzNjA2IC0wLjEzMjIsLTAuMDQ4MDcgbCAtMC4xMjAxODIsLTAuMDQ4MDcgYyAtMC4wOTYxNSwtMC4wMzYwNSAtMC4yMDQzMDksLTAuMDg0MTMgLTAuMzAwNDU1LC0wLjEyMDE4MSAtMC4wNDgwNywtMC4wMjQwNCAtMC4wOTYxNCwtMC4wMzYwNSAtMC4xNTYyMzYsLTAuMDYwMDkgbCAtMC4xMDgxNjMsLTAuMDQ4MDcgYyAtMC4wOTYxNSwtMC4wMzYwNSAtMC4xOTIyOTEsLTAuMDcyMTEgLTAuMjc2NDE5LC0wLjEwODE2NCAtMC4wNjAwOSwtMC4wMjQwNCAtMC4xMDgxNjMsLTAuMDM2MDUgLTAuMTY4MjU0LC0wLjA2MDA5IGwgLTAuMTA4MTY0LC0wLjAzNjA1IGMgLTAuMDk2MTQsLTAuMDM2MDYgLTAuMTgwMjcyLC0wLjA2MDA5IC0wLjI3NjQxOCwtMC4wOTYxNCAtMC4wNjAwOSwtMC4wMjQwNCAtMC4xMjAxODEsLTAuMDM2MDYgLTAuMTgwMjcyLC0wLjA2MDA5IGwgLTAuMDYwMDksLTAuMDI0MDQgYyAtMC4wOTYxNSwtMC4wMzYwNSAtMC4yMDQzMDksLTAuMDcyMTEgLTAuMzAwNDU1LC0wLjA5NjE0IC0wLjA3MjExLC0wLjAyNDA0IC0wLjEzMjIsLTAuMDM2MDYgLTAuMjA0MzA5LC0wLjA2MDA5IGwgLTAuMDQ4MDcsLTAuMDEyMDIgYyAtMC4wOTYxNSwtMC4wMzYwNSAtMC4yMDQzMDksLTAuMDYwMDkgLTAuMzAwNDU1LC0wLjA5NjE1IC0wLjA3MjExLC0wLjAyNDA0IC0wLjE1NjIzNiwtMC4wNDgwNyAtMC4yMjgzNDUsLTAuMDcyMTEgbCAtMC4wOTYxNSwtMC4wMjQwNCBjIC0wLjAxMjAyLDAgLTAuMDM2MDUsLTAuMDEyMDIgLTAuMDQ4MDcsLTAuMDEyMDIgdiAwIGMgMCwtMC4wMTIwMiAtMC4wMTIwMiwtMC4wMzYwNSAtMC4wMTIwMiwtMC4wNDgwNyBsIC0wLjAyNDA0LC0wLjA5NjE0IEMgMTMuNzg4ODIsOS45OTE4NDQ3IDEzLjc2NDc4Myw5LjkwNzcxNzUgMTMuNzQwNzQ3LDkuODM1NjA4NCAxMy43MTY3MTEsOS43Mzk0NjMgMTMuNjgwNjU2LDkuNjQzMzE3NiAxMy42NTY2Miw5LjU0NzE3MjEgTCAxMy42NDQ2LDkuNDk5MDk5MSBDIDEzLjYyMDU2LDkuNDI2OTkwMSAxMy42MDg1NCw5LjM2Njg5OTIgMTMuNTg0NTEsOS4yOTQ3OTAxIDEzLjU2MDQ3LDkuMTk4NjQ1MSAxMy41MjQ0Miw5LjExNDUxNzUgMTMuNTAwMzgsOS4wMTgzNzIxIEwgMTMuNDc2MzQsOC45NDYyNjMxIEMgMTMuNDUyMyw4Ljg4NjE3MjEgMTMuNDI4MjcsOC44MTQwNjMxIDEzLjQwNDIzLDguNzUzOTcyMyAxMy4zODAxOSw4LjY2OTg0NTMgMTMuMzQ0MTQsOC41ODU3MTc4IDEzLjMwODA5LDguNDg5NTcyNCBMIDEzLjI2MDAyLDguMzY5MzkwOCBDIDEzLjIzNTk4LDguMzA5Mjk5OCAxMy4yMjM5Nyw4LjI2MTIyNzIgMTMuMTk5OTMsOC4yMDExMzYzIDEzLjE2Mzg4LDguMTE3MDA5MyAxMy4xMzk4NCw4LjAzMjg4MTggMTMuMTAzNzksNy45NDg3NTQ2IEwgMTMuMDU1NzIsNy44MTY1NTQ3IEMgMTMuMDMxNjgsNy43NTY0NjM3IDEzLjAxOTY2LDcuNzA4MzkxMSAxMi45OTU2Myw3LjY0ODMwMDIgMTIuOTU5NTgsNy41NjQxNzMyIDEyLjkyMzUyLDcuNDY4MDI3NSAxMi44ODc0NjcsNy4zODM5MDAzIGwgLTAuMDYwMDksLTAuMTMyMiBjIC0wLjAyNDA0LC0wLjA0ODA3MyAtMC4wMzYwNSwtMC4wOTYxNDUgLTAuMDYwMDksLTAuMTQ0MjE4MSAtMC4wNDgwNywtMC4xMDgxNjM2IC0wLjA5NjE1LC0wLjIxNjMyNzIgLTAuMTMyMiwtMC4zMjQ0OTA4IEwgMTIuNTg3MDAyLDYuNjYyODA5NyBDIDEyLjU3NDk4NCw2LjYyNjc1NTEgMTIuNTUwOTQ4LDYuNTkwNzAwNiAxMi41Mzg5MjksNi41NTQ2NDYxIDEyLjQ1NDgwMiw2LjM3NDM3MzQgMTIuMzgyNjkzLDYuMTk0MTAwOCAxMi4yOTg1NjYsNi4wMTM4MjgxIDEyLjIyNjQ1Nyw1Ljg2OTYxIDEyLjE2NjM2Niw1LjcyNTM5MTkgMTIuMDgyMjM5LDUuNTgxMTczNyBsIC0wLjAzNjA1LC0wLjA4NDEyNyBDIDExLjk4NjA5NCw1LjM4ODg4MjkgMTEuOTI2MDAzLDUuMjY4NzAxMSAxMS44Nzc5Myw1LjE2MDUzNzUgMTUuMDI2NjkyLDIuNDU2NDQ3NyAxNy45OTUxODIsMi4wNTk4NDc5IDIwLjAyNjI1NCw0LjA3ODkwMTYgWiBtIC0xLjc0MjYzNSw4Ljg1NzM5NjQgYyAtMC4wODQxMywwLjA4NDEzIC0wLjE2ODI1NSwwLjE2ODI1NSAtMC4yNTIzODIsMC4yNTIzODIgLTAuMTU2MjM2LDAuMTU2MjM2IC0wLjMwMDQ1NCwwLjMwMDQ1NSAtMC40NTY2OTEsMC40NDQ2NzMgLTAuMDI0MDQsMC4wMjQwNCAtMC4wNjAwOSwwLjA2MDA5IC0wLjA5NjE0LDAuMDg0MTMgbCAtMC4wNjAwOSwwLjA0ODA3IGMgLTAuMTA4MTY0LDAuMDk2MTQgLTAuMjE2MzI3LDAuMjA0MzA5IC0wLjMyNDQ5MSwwLjMwMDQ1NCAtMC4wNDgwNywwLjAzNjA1IC0wLjA5NjE0LDAuMDg0MTMgLTAuMTMyMiwwLjEyMDE4MiBsIC0wLjAzNjA1LDAuMDM2MDUgYyAtMC4wOTYxNSwwLjA4NDEzIC0wLjIwNDMwOSwwLjE4MDI3MyAtMC4zMDA0NTUsMC4yNjQ0IC0wLjA2MDA5LDAuMDQ4MDcgLTAuMTIwMTgyLDAuMTA4MTY0IC0wLjE5MjI5MSwwLjE1NjIzNyAtMC4wOTYxNCwwLjA4NDEzIC0wLjIwNDMwOSwwLjE2ODI1NCAtMC4zMDA0NTQsMC4yNTIzODEgLTAuMDYwMDksMC4wNDgwNyAtMC4xMjAxODIsMC4xMDgxNjQgLTAuMTkyMjkxLDAuMTU2MjM3IC0wLjEwODE2MywwLjA4NDEzIC0wLjIwNDMwOSwwLjE2ODI1NCAtMC4zMTI0NzIsMC4yNTIzODEgbCAtMC4wNjAwOSwwLjA0ODA3IGMgLTAuMDM2MDUsMC4wMzYwNSAtMC4wODQxMywwLjA2MDA5IC0wLjEyMDE4MiwwLjA5NjE0IC0wLjEyMDE4MiwwLjA5NjE1IC0wLjI0MDM2NCwwLjE4MDI3MyAtMC4zNzI1NjQsMC4yNzY0MTggbCAtMC4wNjAwOSwwLjAzNjA1IGMgLTAuMDI0MDQsMC4wMTIwMiAtMC4wNDgwNywwLjAzNjA2IC0wLjA3MjExLDAuMDQ4MDcgLTAuMDk2MTQsMC4wNzIxMSAtMC4xOTIyOSwwLjE0NDIxOCAtMC4yODg0MzYsMC4yMDQzMDkgMCwtMC4xMjAxODIgMCwtMC4yNDAzNjQgLTAuMDEyMDIsLTAuMzYwNTQ2IHYgLTAuMTIwMTgxIGMgMCwtMC4xODAyNzMgLTAuMDEyMDIsLTAuMzYwNTQ2IC0wLjAyNDA0LC0wLjU1MjgzNiB2IC0wLjAzNjA1IGMgLTAuMDEyMDIsLTAuMTkyMjkxIC0wLjAyNDA0LC0wLjM4NDU4MiAtMC4wNDgwNywtMC41NzY4NzMgbCAtMC4wMTIwMiwtMC4xMjAxODEgYyAtMC4wMTIwMiwtMC4xOTIyOTEgLTAuMDM2MDUsLTAuMzg0NTgyIC0wLjA2MDA5LC0wLjU2NDg1NSBsIC0wLjAxMjAyLC0wLjA3MjExIGMgLTAuMDI0MDQsLTAuMTgwMjcyIC0wLjA0ODA3LC0wLjM0ODUyNyAtMC4wNzIxMSwtMC41Mjg3OTkgbCAtMC4wMTIwMiwtMC4xMDgxNjQgYyAtMC4wMjQwNCwtMC4xOTIyOTEgLTAuMDYwMDksLTAuMzcyNTY0IC0wLjA5NjE0LC0wLjU2NDg1NCBsIC0wLjAyNDA0LC0wLjEyMDE4MiBjIC0wLjAzNjA1LC0wLjE4MDI3MyAtMC4wNzIxMSwtMC4zNjA1NDYgLTAuMTA4MTY0LC0wLjU0MDgxOCBsIC0wLjAxMjAyLC0wLjA0ODA3IGMgLTAuMDM2MDUsLTAuMTQ0MjE4IC0wLjA2MDA5LC0wLjI4ODQzNiAtMC4wOTYxNCwtMC40NDQ2NzMgMC4wOTYxNCwwLjAyNDA0IDAuMTkyMjksMC4wNjAwOSAwLjI4ODQzNiwwLjA4NDEzIGwgMC4wOTYxNCwwLjAzNjA1IGMgMC4xMzIyLDAuMDQ4MDcgMC4yNzY0MTgsMC4wODQxMyAwLjQwODYxOCwwLjEzMjIgbCAwLjE4MDI3MywwLjA2MDA5IGMgMC4xMDgxNjMsMC4wMzYwNSAwLjIxNjMyNywwLjA3MjExIDAuMzM2NTA5LDAuMTIwMTgyIDAuMDYwMDksMC4wMjQwNCAwLjEwODE2MywwLjAzNjA1IDAuMTY4MjU0LDAuMDYwMDkgbCAwLjAyNDA0LDAuMDEyMDIgYyAwLjEwODE2MywwLjAzNjA1IDAuMjA0MzA5LDAuMDg0MTMgMC4zMTI0NzIsMC4xMjAxODEgMC4wNzIxMSwwLjAyNDA0IDAuMTMyMiwwLjA0ODA3IDAuMjA0MzA5LDAuMDcyMTEgMC4xMDgxNjQsMC4wMzYwNSAwLjIxNjMyNywwLjA4NDEzIDAuMzEyNDczLDAuMTMyMiBsIDAuMDQ4MDcsMC4wMjQwNCBjIDAuMDQ4MDcsMC4wMjQwNCAwLjA5NjE0LDAuMDM2MDUgMC4xNDQyMTgsMC4wNjAwOSAwLjEyMDE4MSwwLjA0ODA3IDAuMjI4MzQ1LDAuMDk2MTUgMC4zNDg1MjcsMC4xNDQyMTggbCAwLjE1NjIzNiwwLjA3MjExIGMgMC4xNjgyNTUsMC4wNzIxMSAwLjMzNjUwOSwwLjE0NDIxOCAwLjUwNDc2NCwwLjIyODM0NiAwLjEyMDE4MSwwLjA2MDA5IDAuMjQwMzYzLDAuMTIwMTgxIDAuMzYwNTQ1LDAuMTY4MjU0IGwgMC4wOTYxNCwwLjA0ODA3IGMgMC4xMDgxNjQsMC4wNDgwNyAwLjE2ODI1NSwwLjA4NDEzIDAuMjI4MzQ2LDAuMTA4MTYzIHogTSAyLjQzMTY0MjgsMTEuMTA5NTM1IGMgMC4wNDgwNzMsLTAuMDEyMDIgMC4wOTYxNDUsLTAuMDM2MDUgMC4xNDQyMTgxLC0wLjA0ODA3IGwgMC4wNzIxMDksLTAuMDI0MDQgYyAwLjA3MjEwOSwtMC4wMjQwNCAwLjE0NDIxODEsLTAuMDQ4MDcgMC4yMTYzMjcyLC0wLjA2MDA5IGwgLTAuMDM2MDU1LC0wLjEyMDE4MiAwLjA0ODA3MywwLjEwODE2NCBjIDAuMDg0MTI3LC0wLjAyNDA0IDAuMTY4MjU0NSwtMC4wNDgwNyAwLjI1MjM4MTgsLTAuMDcyMTEgbCAwLjA3MjEwOSwtMC4wMjQwNCBjIDAuMDYwMDkxLC0wLjAxMjAyIDAuMTIwMTgxOCwtMC4wMzYwNiAwLjE5MjI5MDksLTAuMDQ4MDcgbCAwLjA5NjE0NSwtMC4wMjQwNCBjIDAuMDYwMDkxLC0wLjAxMjAyIDAuMTMyMTk5OSwtMC4wMzYwNSAwLjE5MjI5MDgsLTAuMDQ4MDcgbCAwLjA4NDEyNywtMC4wMjQwNCBjIDAuMDk2MTQ1LC0wLjAyNDA0IDAuMTkyMjkwOSwtMC4wMzYwNSAwLjI4ODQzNjMsLTAuMDYwMDkgMC4wOTYxNDUsLTAuMDI0MDQgMC4yMDQzMDksLTAuMDM2MDYgMC4zMTI0NzI2LC0wLjA2MDA5IGwgMC4wOTYxNDUsLTAuMDEyMDIgYyAwLjA3MjEwOSwtMC4wMTIwMiAwLjE1NjIzNjMsLTAuMDI0MDQgMC4yMjgzNDU0LC0wLjAzNjA1IGwgMC4xMDgxNjM2LC0wLjAxMjAyIGMgMC4wNzIxMDksLTAuMDEyMDIgMC4xNTYyMzYzLC0wLjAyNDA0IDAuMjI4MzQ1MywtMC4wMzYwNiBsIDAuMTA4MTYzNiwtMC4wMTIwMiBjIDAuMTA4MTYzNiwtMC4wMTIwMiAwLjIxNjMyNzIsLTAuMDI0MDQgMC4zMzY1MDksLTAuMDQ4MDcgaCAwLjAxMjAxOCBjIDAuMTIwMTgxNywtMC4wMTIwMiAwLjI0MDM2MzUsLTAuMDI0MDQgMC4zNjA1NDUzLC0wLjAzNjA1IGwgMC4xMDgxNjM2LC0wLjAxMjAyIGMgMC4wODQxMjcsLTAuMDEyMDIgMC4xODAyNzI2LC0wLjAxMjAyIDAuMjY0Mzk5OSwtMC4wMjQwNCBoIDAuMDEyMDE4IEMgNi4xNjkyOTU4LDEwLjQ4NDU5IDYuMTA5MjA1LDEwLjU4MDczNSA2LjA0OTExNDEsMTAuNjg4ODk5IGwgLTAuMDYwMDkxLDAuMDk2MTQgYyAtMC4wODQxMjcsMC4xNTYyMzcgLTAuMTY4MjU0NSwwLjMxMjQ3MyAtMC4yNTIzODE3LDAuNDY4NzA5IGwgLTAuMDI0MDM2LDAuMDQ4MDcgYyAtMC4wODQxMjcsMC4xNjgyNTQgLTAuMTgwMjcyNiwwLjMzNjUwOSAtMC4yNjQzOTk5LDAuNDkyNzQ1IGwgLTAuMDQ4MDczLDAuMTA4MTY0IEMgNS4zMTYwMDUzLDEyLjA3MDk5IDUuMjMxODc4LDEyLjIzOTI0NCA1LjE1OTc2OSwxMi40MDc0OTkgbCAtMC4wMTIwMTgsMC4wMzYwNSBDIDUuMDc1NjQyLDEyLjU5OTc4NSA1LjAwMzUzMjksMTIuNzY4MDQgNC45MzE0MjM4LDEyLjkyNDI3NiBsIC0wLjA0ODA3MywwLjA5NjE1IGMgLTAuMDcyMTA5LDAuMTY4MjU0IC0wLjE0NDIxODEsMC4zMzY1MDkgLTAuMjA0MzA5LDAuNTA0NzYzIGwgLTAuMDM2MDU0LDAuMDk2MTUgYyAtMC4wNjAwOTEsMC4xNTYyMzYgLTAuMTIwMTgxOCwwLjMxMjQ3MiAtMC4xODAyNzI3LDAuNDY4NzA4IGwgLTAuMDI0MDM2LDAuMDcyMTEgQyA0LjQwMjYyMzgsMTQuMjcwMzE2IDQuMzU0NTUxMSwxNC4zOTA0OTggNC4zMTg0OTY2LDE0LjQ5ODY2MSAzLjM0NTAyNDMsMTMuMTUyNjI2IDIuNzU2MTMzNiwxMS45Mzg3ODkgMi40MzE2NDI4LDExLjEwOTUzNSBaIE0gMTAuNTA3ODU4LDUuMDUyMzczOSBDIDEwLjM5OTY5NCw1LjE2MDUzNzUgMTAuMjkxNTMxLDUuMjU2NjgzIDEwLjE4MzM2Nyw1LjM2NDg0NjYgMTAuMDI3MTMxLDUuNTIxMDgyOSA5Ljg1ODg3NjIsNS42ODkzMzczIDkuNzAyNjM5OSw1Ljg1NzU5MTggOS42NjY1ODU0LDUuODkzNjQ2MyA5LjYzMDUzMDgsNS45Mjk3MDA5IDkuNjA2NDk0NSw1Ljk2NTc1NTQgTCA5LjQ5ODMzMDksNi4wNzM5MTkgQyA5LjQxNDIwMzYsNi4xNzAwNjQ0IDkuMzE4MDU4Miw2LjI2NjIwOTggOS4yMzM5MzEsNi4zNTAzMzcxIDkuMTk3ODc2NSw2LjM5ODQwOTggOS4xNDk4MDM3LDYuNDM0NDY0MyA5LjExMzc0OTIsNi40ODI1MzcgbCAtMC4xMjAxODE4LDAuMTMyMiBDIDguOTIxNDU4NCw2LjY5ODg2NDIgOC44NDkzNDkzLDYuNzcwOTczMyA4Ljc4OTI1ODQsNi44NTUxMDA1IDguNzQxMTg1Nyw2LjkwMzE3MzIgOC43MDUxMzEyLDYuOTYzMjY0MSA4LjY1NzA1ODUsNy4wMTEzMzY4IEwgOC41NDg4OTQ5LDcuMTQzNTM2NyBjIC0wLjA2MDA5MSwwLjA3MjEwOSAtMC4xMzIyLDAuMTU2MjM2MyAtMC4xOTIyOTA4LDAuMjI4MzQ1NCAtMC4wNDgwNzMsMC4wNjAwOTEgLTAuMDk2MTQ2LDAuMTA4MTYzNiAtMC4xMzIyLDAuMTY4MjU0NSBsIC0wLjA5NjE0NSwwLjEyMDE4MTggYyAtMC4wNjAwOTEsMC4wODQxMjcgLTAuMTMyMTk5OSwwLjE1NjIzNjMgLTAuMTkyMjkwOCwwLjI0MDM2MzUgLTAuMDQ4MDczLDAuMDYwMDkxIC0wLjA5NjE0NSwwLjEyMDE4MTggLTAuMTMyMiwwLjE4MDI3MjcgTCA3LjcxOTY0MDcsOC4yMDExMzYzIEMgNy42NTk1NDk4LDguMjczMjQ1NCA3LjU5OTQ1ODksOC4zNTczNzI2IDcuNTM5MzY4LDguNDI5NDgxNyA3LjQ5MTI5NTMsOC40ODk1NzI2IDcuNDQzMjIyNiw4LjU0OTY2MzUgNy40MDcxNjgxLDguNjIxNzcyNSBsIC0wLjA3MjEwOSwwLjA5NjE0NSBjIC0wLjA2MDA5MSwwLjA4NDEyNyAtMC4xMjAxODE4LDAuMTY4MjU0NSAtMC4xODAyNzI2LDAuMjUyMzgxOCAtMC4wNDgwNzMsMC4wNzIxMDkgLTAuMDk2MTQ1LDAuMTMyMTk5OSAtMC4xMzIyLDAuMjA0MzA5IGwgLTAuMDg0MTI3LDAuMTMyMTk5OSBjIC0wLjAxMjAxOCwwLjAyNDAzNiAtMC4wMzYwNTUsMC4wNDgwNzMgLTAuMDQ4MDczLDAuMDcyMTA5IHYgMCBjIC0wLjAxMjAxOCwwIC0wLjAyNDAzNiwwIC0wLjAzNjA1NSwwIGggLTAuMDk2MTQ1IGMgLTAuMDQ4MDczLDAgLTAuMDk2MTQ1LDAgLTAuMTQ0MjE4MSwwLjAxMjAxOCAtMC4wODQxMjcsMCAtMC4xODAyNzI3LDAuMDEyMDE4IC0wLjI2NDM5OTksMC4wMTIwMTggbCAtMC4xNTYyMzYzLDAuMDEyMDE4IGMgLTAuMDk2MTQ1LDAuMDEyMDE4IC0wLjE5MjI5MDgsMC4wMTIwMTggLTAuMzAwNDU0NCwwLjAyNDAzNiBsIC0wLjEwODE2MzYsMC4wMTIwMTggYyAtMC4xMzIyLDAuMDEyMDE4IC0wLjI2NDM5OTksMC4wMjQwMzYgLTAuMzk2NTk5OSwwLjA0ODA3MyBsIC0wLjA3MjEwOSwwLjAxMjAxOCBjIC0wLjEwODE2MzYsMC4wMTIwMTggLTAuMjA0MzA5LDAuMDI0MDM2IC0wLjMxMjQ3MjYsMC4wMzYwNTQgbCAtMC4xMzIyLDAuMDI0MDM2IGMgLTAuMDg0MTI3LDAuMDEyMDE4IC0wLjE1NjIzNjMsMC4wMjQwMzYgLTAuMjQwMzYzNSwwLjAzNjA1NSBsIC0wLjE0NDIxODEsMC4wMjQwMzYgYyAtMC4wNzIxMDksMC4wMTIwMTggLTAuMTQ0MjE4MiwwLjAyNDAzNiAtMC4yMTYzMjcyLDAuMDM2MDU1IGwgLTAuMTQ0MjE4MSwwLjAyNDAzNiBjIC0wLjA3MjEwOSwwLjAxMjAxOCAtMC4xMzIyLDAuMDI0MDM2IC0wLjE5MjI5MDksMC4wMzYwNTUgTCAzLjgwMTcxNSw5Ljc1MTQ4MTIgYyAtMC4wNjAwOTEsMC4wMTIwMTggLTAuMTMyMiwwLjAyNDAzNiAtMC4xOTIyOTA5LDAuMDM2MDU0IEwgMy40NzcyMjQyLDkuODExNTcyIEMgMy40MTcxMzMyLDkuODIzNTkgMy4zNDUwMjQzLDkuODM1NjA4IDMuMjg0OTMzNCw5Ljg1OTY0NSBMIDMuMTY0NzUxNiw5Ljg4MzY4MSBDIDMuMDkyNjQyNiw5Ljg5NTY5OSAzLjAzMjU1MTcsOS45MTk3MzUgMi45NjA0NDI2LDkuOTMxNzU0IEwgMi44NzYzMTU2LDkuOTU1NzkgYyAtMC4wOTYxNDUsMC4wMjQwMzYgLTAuMTgwMjcyNiwwLjA0ODA3MyAtMC4yNzY0MTgsMC4wNzIxMDkgbCAtMC4wNDgwNzMsMC4wMTIwMiBjIC0wLjA3MjEwOSwwLjAyNDA0IC0wLjE0NDIxODIsMC4wMzYwNSAtMC4yMDQzMDksMC4wNjAwOSBsIC0wLjA5NjE0NSwwLjAyNDA0IGMgLTAuMDQ4MDczLDAuMDEyMDIgLTAuMTA4MTYzNSwwLjAzNjA1IC0wLjE1NjIzNjMsMC4wNDgwNyBsIC0wLjA4NDEyNywwLjAyNDA0IEMgMS4wOTc2MjUxLDguMTA0OTkwOSAwLjc0OTA5Nzk3LDYuMTk0MTAwOCAwLjk4OTQ2MTUxLDQuNjc5ODEwNSB2IDAgQyAxLjI0MTg0MzMsMy4xNDE0ODM4IDIuMDcxMDk3NSwyLjAzNTgxMTUgMy40NTMxODc4LDEuMzg2ODMgbCAwLjAyNDAzNiwtMC4wMTIwMTggYyAwLjYxMjkyNywtMC4yNzY0MTgxIDEuMjI1ODU0MSwtMC40MjA2MzYyIDEuODI2NzYyOSwtMC40MjA2MzYyIDEuOTEwODkwMSwwIDMuNzYxNjg5NCwxLjM1ODA1NCA1LjI4Nzk5ODMsMy45NDE5NjIgeiBNIDQuNzAzMDc4MywxNi41NjU3ODggYyAwLjA4NDEyNywwLjA5NjE0IDAuMTgwMjcyNiwwLjIwNDMwOSAwLjI3NjQxOCwwLjMxMjQ3MiAwLjAzNjA1NSwwLjAzNjA1IDAuMDcyMTA5LDAuMDcyMTEgMC4xMDgxNjM2LDAuMTIwMTgyIGwgMC4wMzYwNTQsMC4wMzYwNiBjIDAuMTA4MTYzNiwwLjEyMDE4MiAwLjIxNjMyNzIsMC4yMjgzNDUgMC4zMjQ0OTA4LDAuMzQ4NTI3IGwgMC4wMzYwNTUsMC4wMzYwNSBjIDAuMDM2MDU0LDAuMDM2MDYgMC4wNzIxMDksMC4wNzIxMSAwLjA5NjE0NSwwLjEwODE2NCAwLjE2ODI1NDUsMC4xODAyNzMgMC4zMjQ0OTA4LDAuMzI0NDkxIDAuNDU2NjkwNywwLjQ1NjY5MSAwLjE0NDIxODEsMC4xNDQyMTggMC4zMDA0NTQ0LDAuMzAwNDU0IDAuNDY4NzA4OSwwLjQ2ODcwOSAwLjAzNjA1NCwwLjAzNjA1IDAuMDcyMTA5LDAuMDcyMTEgMC4xMDgxNjM2LDAuMTA4MTYzIGwgMC4wMzYwNTQsMC4wMzYwNiBjIDAuMTIwMTgxOCwwLjEwODE2MyAwLjI0MDM2MzYsMC4yMjgzNDUgMC4zNjA1NDUzLDAuMzM2NTA5IGwgMC4wMzYwNTUsMC4wMjQwNCBjIDAuMDM2MDU1LDAuMDM2MDUgMC4wODQxMjcsMC4wNzIxMSAwLjEyMDE4MTcsMC4xMDgxNjQgMC4xMDgxNjM2LDAuMDk2MTQgMC4yMjgzNDU0LDAuMTkyMjkxIDAuMzI0NDkwOCwwLjI4ODQzNiAtMS4xMTc2OTA0LDAuMjY0NCAtMi4xOTkzMjY0LDAuNDIwNjM2IC0zLjI0NDkwNzgsMC40Njg3MDkgMC4wMzYwNTUsLTEuMDU3NiAwLjE5MjI5MDksLTIuMTM5MjM2IDAuNDU2NjkwOCwtMy4yNTY5MjYgeiBtIDIuNjA3OTQ0NCwxLjMyMTk5OSBjIC0wLjAzNjA1NSwtMC4wMzYwNSAtMC4wNzIxMDksLTAuMDYwMDkgLTAuMDk2MTQ1LC0wLjA5NjE0IEMgNy4wNDY2MjI4LDE3LjYyMzM4NyA2Ljg3ODM2ODMsMTcuNDY3MTUxIDYuNzIyMTMyLDE3LjI5ODg5NyA2LjU2NTg5NTcsMTcuMTQyNjYgNi40MDk2NTk0LDE2Ljk4NjQyNCA2LjI0MTQwNDksMTYuODE4MTcgNi4yMTczNjg5LDE2Ljc4MjEyIDYuMTgxMzEzOSwxNi43NTgwOCA2LjE1NzI3NzksMTYuNzIyMDIgbCAtMC4wNjAwOTEsLTAuMDcyMTEgYyAtMC4xMDgxNjM2LC0wLjEwODE2MyAtMC4yMDQzMDksLTAuMjE2MzI3IC0wLjMxMjQ3MjYsLTAuMzM2NTA5IC0wLjAzNjA1NCwtMC4wNDgwNyAtMC4wNzIxMDksLTAuMDg0MTMgLTAuMTA4MTYzNiwtMC4xMzIyIGwgLTAuMDcyMTA5LC0wLjA4NDEzIEMgNS41MjAzMTQzLDE2LjAwMDkzNCA1LjQzNjE4NzEsMTUuOTA0Nzg4IDUuMzUyMDU5OCwxNS44MDg2NDMgNS4zMDM5ODcxLDE1Ljc2MDU3IDUuMjY3OTMyNiwxNS43MDA0NzkgNS4yMTk4NTk5LDE1LjY1MjQwNiBsIC0wLjA0ODA3MywtMC4wNjAwOSBjIC0wLjA0ODA3MywtMC4wNjAwOSAtMC4wOTYxNDUsLTAuMTIwMTgyIC0wLjE0NDIxODIsLTAuMTY4MjU1IDAuMDYwMDkxLC0wLjIwNDMwOSAwLjEzMjIsLTAuNDA4NjE4IDAuMTkyMjkwOSwtMC41ODg4OTEgbCAwLjAxMjAxOCwtMC4wNDgwNyBjIDAuMDcyMTA5LC0wLjIxNjMyOCAwLjE1NjIzNjMsLTAuNDMyNjU1IDAuMjI4MzQ1NCwtMC42MzY5NjQgdiAtMC4wMTIwMiBDIDUuNTQ0MzUsMTMuOTIxNzg5IDUuNjI4NDc3NSwxMy43MDU0NjIgNS43MTI2MDQ3LDEzLjUwMTE1MyBMIDUuNjA0NDQxNSwxMy40NTMwOCB2IDAgbCAwLjEyMDE4MTgsMC4wMzYwNSBjIDAuMDg0MTI3LC0wLjIwNDMwOSAwLjE4MDI3MjcsLTAuNDA4NjE4IDAuMjc2NDE4MSwtMC42MjQ5NDYgbCAwLjAyNDAzNiwtMC4wNDgwNyBjIDAuMDg0MTI4LC0wLjE5MjI4OCAwLjE4MDI3MywtMC4zODQ1NzkgMC4yODg0MzY2LC0wLjYwMDkwNiBsIDAuMDM2MDU1LC0wLjA2MDA5IGMgMC4wOTYxNDUsLTAuMTgwMjczIDAuMTkyMjkwOCwtMC4zNzI1NjQgMC4zMDA0NTQ0LC0wLjU3Njg3MyBsIDAuMDM2MDU1LC0wLjA3MjExIEMgNi43OTQyNDIsMTEuMzI1ODYyIDYuOTAyNDA1NSwxMS4xMzM1NzIgNy4wMTA1NjkxLDEwLjk0MTI4MSBsIDAuMDQ4MDczLC0wLjA4NDEzIGMgMC4xMDgxNjM2LC0wLjE4MDI3MiAwLjIxNjMyNzIsLTAuMzYwNTQ1IDAuMzM2NTA5LC0wLjU0MDgxOCAwLjIwNDMwOSwwIDAuNDA4NjE4LDAgMC42MDA5MDg5LDAgaCAwLjEwODE2MzUgYyAwLjIxNjMyNzIsMCAwLjQyMDYzNjIsMC4wMTIwMiAwLjYyNDk0NTIsMC4wMjQwNCBoIDAuMDg0MTI3IGMgMC4yMjgzNDU0LDAuMDEyMDIgMC40NDQ2NzI1LDAuMDI0MDQgMC42NDg5ODE1LDAuMDM2MDUgaCAwLjA2MDA5MSBjIDAuMjQwMzYzNiwwLjAyNDA0IDAuNDU2NjkwOCwwLjAzNjA1IDAuNjczMDE3OCwwLjA3MjExIGwgMC4wMTIwMiwtMC4xMjAxODEgdiAwIDAuMTIwMTgxIGMgMC4yMTYzMjgsMC4wMjQwNCAwLjQzMjY1NSwwLjA2MDA5IDAuNjczMDE4LDAuMDk2MTUgbCAwLjA2MDA5LDAuMDEyMDIgYyAwLjIwNDMwOSwwLjAzNjA1IDAuNDIwNjM2LDAuMDcyMTEgMC42NDg5ODIsMC4xMDgxNjQgbCAwLjA4NDEzLDAuMDEyMDIgYyAwLjIwNDMwOSwwLjAzNjA1IDAuNDIwNjM2LDAuMDg0MTMgMC42MzY5NjMsMC4xMzIyIGwgMC4xMDgxNjQsMC4wMjQwNCBjIDAuMjA0MzA5LDAuMDQ4MDcgMC40MDg2MTgsMC4wOTYxNSAwLjYxMjkyNywwLjE0NDIxOCAwLjA0ODA3LDAuMjA0MzA5IDAuMDk2MTQsMC40MjA2MzYgMC4xNDQyMTgsMC42MTI5MjcgbCAwLjAyNDA0LDAuMTA4MTY0IGMgMC4wNDgwNywwLjIxNjMyNyAwLjA5NjE1LDAuNDMyNjU1IDAuMTMyMiwwLjYzNjk2NCBsIDAuMDEyMDIsMC4wOTYxNCBjIDAuMDQ4MDcsMC4yNDAzNjQgMC4wODQxMywwLjQ0NDY3MyAwLjEwODE2NCwwLjY2MSBsIDAuMDEyMDIsMC4wNjAwOSBjIDAuMDM2MDYsMC4yNDAzNjMgMC4wNjAwOSwwLjQ2ODcwOSAwLjA5NjE0LDAuNjg1MDM2IHYgMC4wMTIwMiBjIDAuMDI0MDQsMC4yMTYzMjcgMC4wNDgwNywwLjQ0NDY3MiAwLjA3MjExLDAuNjczMDE4IHYgMC4wNjAwOSBjIDAuMDEyMDIsMC4yMDQzMDkgMC4wMzYwNSwwLjQyMDYzNiAwLjAzNjA1LDAuNjYwOTk5IHYgMC4wODQxMyBjIDAuMDEyMDIsMC4yMDQzMDkgMC4wMTIwMiwwLjQwODYxOCAwLjAxMjAyLDAuNjM2OTYzIHYgMC4wOTYxNCBjIDAsMC4yMDQzMDkgMCwwLjQwODYxOCAwLDAuNjEyOTI3IC0wLjE4MDI3MiwwLjEwODE2NCAtMC4zNjA1NDUsMC4yMjgzNDYgLTAuNTQwODE4LDAuMzI0NDkxIGwgLTAuMDg0MTMsMC4wNDgwNyBjIC0wLjE5MjI5MSwwLjEwODE2NCAtMC4zNzI1NjMsMC4yMTYzMjcgLTAuNTUyODM2LDAuMzI0NDkxIGwgLTAuMDcyMTEsMC4wMzYwNSBjIC0wLjE5MjI5MSwwLjEwODE2NCAtMC4zODQ1ODIsMC4yMDQzMDkgLTAuNTc2ODcyLDAuMzAwNDU1IGwgLTAuMDYwMDksMC4wMzYwNSBjIC0wLjIwNDMwOSwwLjEwODE2NCAtMC4zOTY2LDAuMjA0MzA5IC0wLjU4ODg5MSwwLjI4ODQzNiBsIC0wLjA0ODA3LDAuMDI0MDQgYyAtMC4yMTYzMjcsMC4wOTYxNCAtMC40MjA2MzYsMC4xOTIyOTEgLTAuNjI0OTQ1LDAuMjc2NDE4IGggLTAuMDEyMDIgYyAtMC4yMDQzMDksMC4wODQxMyAtMC40MjA2MzYsMC4xNjgyNTQgLTAuNjM2OTYzNSwwLjI1MjM4MiBoIC0wLjAxMjAxOCBjIC0wLjIwNDMwOSwwLjA3MjExIC0wLjM5NjU5OTksMC4xNDQyMTggLTAuNjI0OTQ1MiwwLjIyODM0NSBsIC0wLjA0ODA3MywwLjAyNDA0IGMgLTAuMTkyMjkwOSwwLjA2MDA5IC0wLjM4NDU4MTcsMC4xMzIyIC0wLjU4ODg5MDcsMC4xOTIyOTEgLTAuMDYwMDkxLC0wLjA0ODA3IC0wLjEyMDE4MTgsLTAuMDk2MTQgLTAuMTgwMjcyNywtMC4xNDQyMTggbCAtMC4wNjAwOTEsLTAuMDQ4MDcgQyA4LjMyMDU0OTUsMTguODAxMTY5IDguMjcyNDc2OCwxOC43NTMwOTYgOC4yMjQ0MDQxLDE4LjcxNzA0MiA4LjEyODI1ODcsMTguNjMyOTE0IDguMDMyMTEzMywxOC41NDg3ODcgNy45MzU5Njc5LDE4LjQ2NDY2IGwgLTAuMDk2MTQ1LC0wLjA4NDEzIGMgLTAuMDQ4MDczLC0wLjAzNjA2IC0wLjA4NDEyNywtMC4wNzIxMSAtMC4xMzIyLC0wLjEyMDE4MiAtMC4xMDgxNjQsLTAuMDk2MTQgLTAuMjE2MzI3NiwtMC4xOTIyODggLTAuMzEyNDczLC0wLjMwMDQ1MiB6IG0gMi41ODM5MDgsMS43NDI2MzYgMC4wNzIxMDksLTAuMDI0MDQgYyAwLjE1NjIzNjMsLTAuMDYwMDkgMC4zMDA0NTQzLC0wLjEwODE2NCAwLjQ1NjY5MTMsLTAuMTY4MjU1IGwgMC4wOTYxNCwtMC4wMzYwNSBjIDAuMTY4MjU0LC0wLjA3MjExIDAuMzI0NDkxLC0wLjEzMjIgMC40OTI3NDUsLTAuMjA0MzA5IGwgMC4xMDgxNjQsLTAuMDQ4MDcgYyAwLjE1NjIzNiwtMC4wNzIxMSAwLjMxMjQ3MiwtMC4xMzIyIDAuNDY4NzA5LC0wLjIwNDMwOSBsIDAuMDQ4MDcsLTAuMDI0MDQgYyAwLjE2ODI1NSwtMC4wNzIxMSAwLjMyNDQ5MSwtMC4xNTYyMzcgMC40OTI3NDUsLTAuMjQwMzY0IGwgMC4xMDgxNjQsLTAuMDYwMDkgYyAwLjE2ODI1NCwtMC4wODQxMyAwLjMyNDQ5MSwtMC4xNjgyNTQgMC40OTI3NDUsLTAuMjUyMzgyIGwgMC4wNDgwNywtMC4wMjQwNCBjIDAuMTQ0MjE4LC0wLjA4NDEzIDAuMzAwNDU0LC0wLjE2ODI1NCAwLjQ0NDY3MywtMC4yNTIzODIgbCAwLjEwODE2MywtMC4wNjAwOSBjIDAuMDk2MTUsLTAuMDYwMDkgMC4yMDQzMDksLTAuMTIwMTgxIDAuMzAwNDU1LC0wLjE4MDI3MiAtMC4wMTIwMiwwLjA4NDEzIC0wLjAxMjAyLDAuMTgwMjcyIC0wLjAyNDA0LDAuMjY0NCBsIC0wLjAxMjAyLDAuMTA4MTYzIGMgLTAuMDEyMDIsMC4xMjAxODIgLTAuMDI0MDQsMC4yNDAzNjQgLTAuMDM2MDYsMC4zNjA1NDYgbCAwLjEyMDE4MiwwLjAxMjAyIGggLTAuMTIwMTgyIGMgLTAuMDEyMDIsMC4xMDgxNjMgLTAuMDI0MDQsMC4yMTYzMjcgLTAuMDQ4MDcsMC4zMjQ0OTEgbCAtMC4wMTIwMiwwLjEwODE2MyBjIC0wLjAxMjAyLDAuMDcyMTEgLTAuMDI0MDQsMC4xNTYyMzYgLTAuMDM2MDYsMC4yMjgzNDYgbCAtMC4wMjQwNCwwLjEyMDE4MSBjIC0wLjAxMjAyLDAuMDcyMTEgLTAuMDI0MDQsMC4xNDQyMTggLTAuMDM2MDYsMC4yMTYzMjcgbCAtMC4wMTIwMiwwLjA5NjE1IGMgLTAuMDI0MDQsMC4wOTYxNCAtMC4wMzYwNSwwLjIwNDMwOSAtMC4wNjAwOSwwLjMwMDQ1NCBsIDAuMTIwMTgyLDAuMDI0MDQgaCAtMC4xMjAxODIgYyAtMC4wMjQwNCwwLjA5NjE0IC0wLjAzNjA1LDAuMTgwMjcyIC0wLjA2MDA5LDAuMjY0NCBsIC0wLjAyNDA0LDAuMDg0MTMgYyAtMC4wMTIwMiwwLjA2MDA5IC0wLjAzNjA1LDAuMTMyMiAtMC4wNDgwNywwLjE5MjI5MSBsIC0wLjAyNDA0LDAuMDg0MTMgYyAtMC4wMTIwMiwwLjA2MDA5IC0wLjAzNjA2LDAuMTMyMiAtMC4wNDgwNywwLjE5MjI5MSBsIC0wLjAyNDA0LDAuMDcyMTEgYyAtMC4wMjQwNCwwLjA4NDEzIC0wLjA0ODA3LDAuMTY4MjU0IC0wLjA3MjExLDAuMjUyMzgxIC0wLjAyNDA0LDAuMDg0MTMgLTAuMDQ4MDcsMC4xNTYyMzcgLTAuMDcyMTEsMC4yMjgzNDYgbCAtMC4wMjQwNCwwLjA2MDA5IGMgLTAuMDEyMDIsMC4wNDgwNyAtMC4wMzYwNSwwLjEwODE2MyAtMC4wNDgwNywwLjE1NjIzNiAtMC44MTcyMzYsLTAuMzM2NTA5IC0yLjAwNzAzNiwtMC45MTMzODEgLTMuMzUzMDcxNywtMS44NjI4MTcgMC4xMzIyLC0wLjAzNjA1IDAuMjQwMzYzNSwtMC4wNzIxMSAwLjM2MDU0NTMsLTAuMTA4MTY0IHogbSAzLjkxNzkyNTMsMi4yODM0NTQgYyAwLjAxMjAyLC0wLjA0ODA3IDAuMDM2MDUsLTAuMTA4MTY0IDAuMDQ4MDcsLTAuMTU2MjM3IGwgMC4wMjQwNCwtMC4wODQxMyBjIDAuMDI0MDQsLTAuMDcyMTEgMC4wNDgwNywtMC4xNDQyMTggMC4wNjAwOSwtMC4yMTYzMjcgbCAwLjAxMjAyLC0wLjAzNjA2IGMgMC4wMjQwNCwtMC4wODQxMyAwLjA0ODA3LC0wLjE4MDI3MiAwLjA3MjExLC0wLjI3NjQxOCBsIDAuMDI0MDQsLTAuMDg0MTMgYyAwLjAxMjAyLC0wLjA3MjExIDAuMDM2MDUsLTAuMTMyMiAwLjA0ODA3LC0wLjIwNDMwOSBsIDAuMDI0MDQsLTAuMTIwMTgyIGMgMC4wMTIwMiwtMC4wNjAwOSAwLjAyNDA0LC0wLjEyMDE4MSAwLjA0ODA3LC0wLjE4MDI3MiBsIDAuMDI0MDQsLTAuMTMyMiBjIDAuMDEyMDIsLTAuMDYwMDkgMC4wMjQwNCwtMC4xMjAxODIgMC4wMzYwNSwtMC4xODAyNzMgbCAwLjAyNDA0LC0wLjE0NDIxOCBjIDAuMDEyMDIsLTAuMDYwMDkgMC4wMjQwNCwtMC4xMjAxODIgMC4wMzYwNSwtMC4xOTIyOTEgbCAwLjAyNDA0LC0wLjE0NDIxOCBjIDAuMDEyMDIsLTAuMDYwMDkgMC4wMjQwNCwtMC4xMzIyIDAuMDM2MDUsLTAuMjA0MzA5IHYgLTAuMDEyMDIgYyAwLjAxMjAyLC0wLjA0ODA3IDAuMDEyMDIsLTAuMDk2MTUgMC4wMjQwNCwtMC4xMzIyIDAuMDEyMDIsLTAuMDcyMTEgMC4wMjQwNCwtMC4xNDQyMTggMC4wMzYwNSwtMC4yMTYzMjcgbCAwLjAyNDA0LC0wLjE0NDIxOCBjIDAuMDEyMDIsLTAuMDg0MTMgMC4wMjQwNCwtMC4xODAyNzMgMC4wMzYwNSwtMC4yNzY0MTkgbCAwLjAxMjAyLC0wLjA5NjE1IGMgMC4wMTIwMiwtMC4xMjAxODIgMC4wMjQwNCwtMC4yNTIzODIgMC4wNDgwNywtMC4zODQ1ODIgbCAwLjAxMjAyLC0wLjEzMjIgYyAwLjAxMjAyLC0wLjA4NDEzIDAuMDEyMDIsLTAuMTgwMjcyIDAuMDI0MDQsLTAuMjc2NDE4IGwgMC4wMTIwMiwtMC4xNjgyNTQgYyAwLjAxMjAyLC0wLjA4NDEzIDAuMDEyMDIsLTAuMTY4MjU1IDAuMDEyMDIsLTAuMjUyMzgyIDAsLTAuMDYwMDkgMC4wMTIwMiwtMC4xMjAxODIgMC4wMTIwMiwtMC4xODAyNzMgdiAtMC4wNDgwNyBjIDAsLTAuMDEyMDIgMCwtMC4wMjQwNCAwLC0wLjAzNjA2IDAuMDI0MDQsLTAuMDI0MDQgMC4wNjAwOSwtMC4wMzYwNSAwLjA4NDEzLC0wLjA2MDA5IGwgMC4xMjAxODIsLTAuMDg0MTMgYyAwLjA3MjExLC0wLjA0ODA3IDAuMTQ0MjE4LC0wLjA5NjE0IDAuMjA0MzA5LC0wLjE0NDIxOCAwLjA4NDEzLC0wLjA2MDA5IDAuMTY4MjU0LC0wLjEyMDE4MiAwLjI1MjM4MiwtMC4xODAyNzMgbCAwLjEwODE2MywtMC4wNzIxMSBjIDAuMDYwMDksLTAuMDQ4MDcgMC4xMjAxODIsLTAuMDg0MTMgMC4xODAyNzMsLTAuMTMyMiAwLjA4NDEzLC0wLjA2MDA5IDAuMTY4MjU0LC0wLjEzMjIgMC4yNTIzODIsLTAuMTkyMjkgbCAwLjA5NjE0LC0wLjA3MjExIGMgMC4wNjAwOSwtMC4wNDgwNyAwLjEyMDE4MiwtMC4wODQxMyAwLjE4MDI3MywtMC4xMzIxOTkgMC4wODQxMywtMC4wNjAwOSAwLjE2ODI1NCwtMC4xMzIyIDAuMjQwMzYzLC0wLjE5MjI5MSBsIDAuMTIwMTgyLC0wLjA5NjE1IGMgMC4wNDgwNywtMC4wNDgwNyAwLjEwODE2NCwtMC4wODQxMyAwLjE1NjIzNiwtMC4xMzIyIDAuMDg0MTMsLTAuMDcyMTEgMC4xNTYyMzcsLTAuMTMyMiAwLjI0MDM2NCwtMC4yMDQzMDkgbCAwLjEzMjIsLTAuMTA4MTYzIGMgMC4wNDgwNywtMC4wMzYwNSAwLjA5NjE0LC0wLjA4NDEzIDAuMTQ0MjE4LC0wLjEyMDE4MiAwLjA4NDEzLC0wLjA3MjExIDAuMTgwMjczLC0wLjE1NjIzNiAwLjI2NDQsLTAuMjI4MzQ1IGwgMC4xMjAxODIsLTAuMTA4MTY0IGMgMC4wMzYwNSwtMC4wMzYwNSAwLjA4NDEzLC0wLjA3MjExIDAuMTIwMTgxLC0wLjEwODE2NCAwLjEyMDE4MiwtMC4xMDgxNjMgMC4yNDAzNjQsLTAuMjI4MzQ1IDAuMzYwNTQ2LC0wLjMzNjUwOSBsIDAuMDYwMDksLTAuMDQ4MDcgYyAwLjAyNDA0LC0wLjAyNDA0IDAuMDYwMDksLTAuMDQ4MDcgMC4wODQxMywtMC4wNzIxMSAwLjE2ODI1NCwtMC4xNTYyMzcgMC4zMzY1MDksLTAuMzI0NDkxIDAuNDkyNzQ1LC0wLjQ4MDcyNyAwLjEwODE2NCwtMC4xMDgxNjQgMC4yMTYzMjcsLTAuMjE2MzI4IDAuMzI0NDkxLC0wLjMyNDQ5MSBMIDE5LjA2NDgsMTMuNDUzMDggYyAzLjM3NzEwNywyLjAwNzAzNiA0LjY1MTAzNCw0LjU5MDk0NCAzLjQ4NTI3MSw3LjA5MDcyNCBsIC0wLjAxMjAyLDAuMDI0MDQgYyAtMC42NDg5ODIsMS4zODIwOSAtMS43NTQ2NTQsMi4yMTEzNDQgLTMuMjkyOTgxLDIuNDYzNzI2IC0xLjUxNDI5LDAuMjUyMzgyIC0zLjQyNTE4LC0wLjEwODE2MyAtNS41MTYzNDMsLTEuMDIxNTQ1IHoiCiAgIGlkPSJwYXRoMSIKICAgc3R5bGU9InN0cm9rZS13aWR0aDoxIiAvPgoKCjwvc3ZnPgo='
		);
		add_action( 'load-' . $dashboard_page_suffix, array( &$this, 'load_page' ) );
	}

	function admin_enqueue_css() {
		global $TAINACAN_BASE_URL;
		wp_admin_css( 'dashboard' );
		wp_enqueue_style( 'tainacan-dashboard-page', $TAINACAN_BASE_URL . '/assets/css/tainacan-dashboard.css', [], TAINACAN_VERSION );
	}

	/**
	 * Enqueue the scripts for the dashboard page, using WordPress existing 'dashboard' and 'postbox' scripts
	 */
	function admin_enqueue_js() {
		 wp_enqueue_script('dashboard');
		 wp_enqueue_script('postbox');
	}

	function load_page() {
		parent::load_page();

		$screen = get_current_screen();
    
		// Safety check
		if (!$screen)
			return;

		// Load the admin dashboard code from core
		require_once ABSPATH . 'wp-admin/includes/dashboard.php';

		// Register Tainacan Cards using WordPress Widgets API
		$this->register_cards();

		// Add help tabs if needed
		$screen->add_help_tab(array(
			'id' => 'tainacan_dashboard_help_tab',
			'title' => __('Dashboard Options', 'your-textdomain'),
			'content' => '<p>' . __('You can customize which widgets appear on this dashboard.', 'your-textdomain') . '</p>',
		));

		$screen->set_help_sidebar(
			'<p>' . __('For more information:', 'your-textdomain') . '</p>' .
			'<p><a href="https://tainacan.org/docs/" target="_blank">' . __('Tainacan Documentation', 'your-textdomain') . '</a></p>'
		);
			
	}

	public function render_page_content() {
		require_once('page.php');
	}

	/**
	 * Registers the deafult dashboard cards to be displayed
	 */
	function register_cards() {

		/**
		 * Option that stores the user disabled cards
		 */
		$this->disabled_cards = get_option(
			'tainacan_dashboard_disabled_cards',
			array()
		);
		
		/**
		 * Filling the array containing the default cards
		 * based on user capabilities
		 */
		if (
			current_user_can( 'manage_tainacan' ) ||
			current_user_can( 'tnc_rep_edit_taxonomies') ||
			current_user_can( 'tnc_rep_edit_metadata') ||
			current_user_can( 'tnc_rep_edit_filters')
		) {
			$tainacan_dashboard_cards[] = array(
				'id' => 'tainacan-dashboard-repository-card',
				'title' => __( 'Repository', 'tainacan' ),
				'description' => __('Area responsible for gathering all the structural settings that affect the collections of your repository.', 'tainacan'),
				'content' => [$this, 'tainacan_repository_dashboard_card'],
				'icon' => $this->get_svg_icon( 'repository' ),
				'color' => 'blue'
			);
		}

		$tainacan_dashboard_cards[] = array(
			'id' => 'tainacan-dashboard-collections-card',
			'title' => __( 'Collections', 'tainacan' ),
			'description' => __('Collections are groups of items in the repository that share the same set of metadata.', 'tainacan'),
			'content' => [$this, 'tainacan_collections_dashboard_card'],
			'icon' => $this->get_svg_icon( 'collections' ),
			'color' => 'turquoise',
			'position' => 'side'
		);

		$tainacan_dashboard_cards[] = array(
			'id' => 'tainacan-dashboard-info-card',
			'title' => __( 'Help content and tutorials', 'tainacan' ),
			'description' => __('The Tainacan community provides some help resources. Below we list the main ones for you to clear your doubts.', 'tainacan'),
			'content' => array( $this, 'tainacan_help_dashboard_card' ),
			'icon' => $this->get_svg_icon( 'info' ),
			'color' => 'gray',
			'position' => 'column3'
		);

		$collections = tainacan_collections()->fetch(array(), 'OBJECT');
		foreach( $collections as $index => $collection ) {
			$tainacan_dashboard_cards[] = array(
				'id' => 'tainacan-dashboard-collection-card-' . $collection->get_id(),
				'title' => $collection->get_name(),
				'description' => $collection->get_description(),
				'content' => array( $this, 'tainacan_collection_dashboard_card' ),
				'content_args' => array( 'collection_id' => $collection->get_id() ),
				'icon' => $this->get_svg_icon( 'collection' ),
				'color' => 'turquoise',
				'position' => ['normal', 'side', 'column3'][$index % 3]
			);
		}

		/**
		 * Use this filter to add or remove dashboard cards.
		 * Remeber to return an array containing the card objects
		 * with a structure that contains the id, title, description,
		 * content (a callback), icon (an svg or img html tag), color
		 * (one of gray, blue and turoquoise) and position (normal, side, column3, column4)
		 * 
		 * If you remove any card from the array, users won't be able to add it anyway.
		 * If you just remove its id from the 'tainacan_dashboard_disabled_cards' wp option, 
		 * users will be able to add it again.
		 */
		$tainacan_dashboard_cards = apply_filters( 'tainacan-dashboard-cards', $tainacan_dashboard_cards );

		foreach ($tainacan_dashboard_cards as $card) {
			if ( in_array( $card['id'], $this->disabled_cards ) )
				continue;
			
			$this->add_dashboard_card(
				$card['id'],
				$card['title'],
				$card
			);
		}
	}

	/**
	 * Wrapper for the wp_add_dashboard_widget function
	 * that also accepts an icon and do not expects controle_callback or control_callback_args
	 * 
	 * @param string $id
	 * @param string $title
	 * @param array $args {
	 *    Optional. Array of arguments for adding a dashboard card.
	 * 		@type string description Summary or small description for the card.
	 * 		@type callable callback function to return HTML content inside the card.
	 * 		@type array $content_args Arguments to be passed to the content callback.
	 * 	 	@type string $icon Icon to be displayed on the card.
	 * 		@type string $color Color of the card. One of 'gray', 'blue', 'turquoise'.
	 * 		@type string $position Position of the card. One of 'normal', 'side', 'column3', 'column4'.
	 * }
	 */
	function add_dashboard_card( $id, $title, $args = array() ) {

		$defaults = array(
			'description' => '',
			'content' => null,
			'content_args' => null,
			'icon' => '',
			'color' => 'gray',
			'position' => 'normal'
		);

		$args = wp_parse_args( $args, $defaults );

		$widget_name = '<span class="tainacan-dashboard-card-title">' . $title . '</span>';
		$widget_name = $args['icon'] ? ('<span class="icon" style="background-color: var(--tainacan-' . $args['color'] . '5);">' . $args['icon'] . '</span>' . $widget_name) : $widget_name;

		$content_callback = $args['content'];
		$callback_args = $args['content_args'];
		$private_callback_args = array( '__widget_basename' => $widget_name );

		if ( is_null( $callback_args ) )
			$callback_args = $private_callback_args;
		elseif ( is_array( $callback_args ) )
			$callback_args = array_merge( $callback_args, $private_callback_args );

		$content_callback = function () use ($args, $callback_args) {
			if ( $args['description'] )
				echo '<p class="tainacan-dashboard-card-description">' . $args['description'] . '</p>';

			echo '<hr>';
			call_user_func($args['content'], $callback_args);
		};
		
		wp_add_dashboard_widget(
			$id,
			$widget_name,
			$content_callback,
			null,
			$callback_args,
			$args['position']
		);

		return $id;
	}


	/**
	 * Creates the display code for the repository card
	 */
	function tainacan_repository_dashboard_card($args = null) {
		?>
		<ul class="tainacan-dashboard-card-list" data-color-scheme="blue">
			<?php if ( current_user_can( 'manage_tainacan' ) ||	current_user_can( 'tnc_rep_edit_taxonomies') ) : ?>
				<li>
					<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/taxonomies'); ?>">
						<span class="icon">
							<?php echo $this->get_svg_icon('taxonomies'); ?>
						</span>
						<span class="text"><?php _e('Taxonomies', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( current_user_can( 'manage_tainacan' ) ||	current_user_can( 'tnc_rep_edit_metadata') ) : ?>
				<li>
					<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/metadata'); ?>">
						<span class="icon">
							<?php echo $this->get_svg_icon('metadata'); ?>
						</span>
						<span class="text"><?php _e('Metadata', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( current_user_can( 'manage_tainacan' ) ||	current_user_can( 'tnc_rep_edit_filters') ) : ?>
				<li>
					<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/filters'); ?>">
						<span class="icon">
							<?php echo $this->get_svg_icon('filters'); ?>
						</span>
						<span class="text"><?php _e('Filters', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<?php if ( current_user_can( 'manage_tainacan' ) ) : ?>
				<li>
					<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/importers'); ?>">
						<span class="icon">
							<?php echo $this->get_svg_icon('importers'); ?>
						</span>
						<span class="text"><?php _e('Importers', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
		</ul>
		<?php
	}

	/**
	 * Creates the display code for the collections card
	 */
	function tainacan_collections_dashboard_card($args = null) {
		?>
		<ul class="tainacan-dashboard-card-list">
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections'); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('collections'); ?>
					</span>
					<span class="text"><?php _e('Collections list', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/items'); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('items'); ?>
					</span>
					<span class="text"><?php _e('Items list', 'tainacan'); ?></span>
				</a>
			</li>
			<?php if ( current_user_can('manage_tainacan') || current_user_can('tnc_rep_edit_collections') ) : ?>
				<li>
					<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/new'); ?>">
						<span class="icon">
							<?php echo $this->get_svg_icon('add'); ?>
						</span>
						<span class="text"><?php _e('New collection', 'tainacan'); ?></span>
					</a>
				</li>
			<?php endif; ?>
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/my-items?' . http_build_query(['authorid' => get_current_user_id()])  ); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('item'); ?>
					</span>
					<span class="text"><?php _e('My Items list', 'tainacan'); ?></span>
				</a>
			</li>
		</ul>
		<?php
	}

	/**
	 * Creates the display code for the info and help card
	 */
	function tainacan_help_dashboard_card($args = null) {
		?>
		<ul class="tainacan-dashboard-card-list" data-color-scheme="gray">
			<li>
				<a href="https://tainacan.discourse.group" target="_blank">
					<span class="icon">
						<?php echo $this->get_svg_icon('discourse'); ?>
					</span>
					<span class="text"><?php _e('User\'s forum', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php _e('https://tainacan.github.io/tainacan-wiki/#/faq', 'tainacan'); ?>" target="_blank">
					<span class="icon">
						<?php echo $this->get_svg_icon('help'); ?>
					</span>
					<span class="text"><?php _e('F.A.Q.', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="https://tainacan.github.io/tainacan-wiki/#/" target="_blank">
					<span class="icon">
						<?php echo $this->get_svg_icon('info'); ?>
					</span>
					<span class="text"><?php _e('Wiki', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="https://github.com/tainacan/tainacan" target="_blank">
					<span class="icon">
						<?php echo $this->get_svg_icon('github'); ?>
					</span>
					<span class="text"><?php _e('GitHub', 'tainacan'); ?></span>
				</a>
			</li>
		</ul>
		<?php
	}
	
	/**
	 * Creates the display code for a collection card
	 */
	function tainacan_collection_dashboard_card($args = null) {
		$collection_id = isset($args['collection_id']) ? $args['collection_id'] : null;

		if ( is_null($collection_id) )
			return;
	
	?>
		<ul class="tainacan-dashboard-card-list">
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/items'); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('items'); ?>
					</span>
					<span class="text"><?php _e('Items list', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/my-items?' . http_build_query(['authorid' => get_current_user_id()]) ); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('item'); ?>
					</span>
					<span class="text"><?php _e('My Items list', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/metadata'); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('metadata'); ?>
					</span>
					<span class="text"><?php _e('Metadata', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/settings'); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('settings'); ?>
					</span>
					<span class="text"><?php _e('Settings', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/filters'); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('filters'); ?>
					</span>
					<span class="text"><?php _e('Filters', 'tainacan'); ?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo admin_url('admin.php?page=' . $this->vue_component_page_slug . '#/collections/' . $collection_id . '/reports'); ?>">
					<span class="icon">
						<?php echo $this->get_svg_icon('reports' ); ?>
					</span>
					<span class="text"><?php _e('Reports', 'tainacan'); ?></span>
				</a>
			</li>
		</ul>
	<?php
	}

}
